<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Category Controller
 * 
 * This controller handles all CRUD operations for transaction categories.
 * Categories are essential for organizing and analyzing financial data -
 * they help users understand spending patterns, create budgets, and generate reports.
 * 
 * Key Features:
 * - Category creation with validation and hierarchy support
 * - Category listing with usage statistics
 * - Category editing with transaction impact analysis
 * - Category deletion with safe transaction handling
 * - Hierarchical category relationships (parent/child)
 * - Category color coding and icons for visual organization
 * - Category analytics and spending insights
 * - Default system categories vs custom user categories
 * - Category import/export functionality
 * - Bulk category operations
 * 
 * Why categories are important:
 * - They enable detailed expense tracking and analysis
 * - They support budget creation and monitoring
 * - They facilitate financial reporting and insights
 * - They help identify spending patterns and trends
 * - They enable goal setting and financial planning
 */
class CategoryController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Require authentication for all category operations
        $this->middleware('auth');
        
        // Require email verification for financial data access
        $this->middleware('verified');
        
        // Check account status (active, not suspended, not locked)
        $this->middleware('check.account.status');
        
        // Apply rate limiting for category operations
        $this->middleware('throttle:50,1')->only(['store', 'update']);
    }

    /**
     * Display a listing of categories.
     * 
     * Shows both system categories and user's custom categories
     * with usage statistics and filtering options.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Build the query for categories available to this user
        $query = Category::forUser($user->id);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply type filter (income/expense)
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply ownership filter
        if ($request->filled('ownership')) {
            if ($request->ownership === 'custom') {
                $query->where('user_id', $user->id);
            } elseif ($request->ownership === 'system') {
                $query->where('is_system', true);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        $allowedSorts = ['name', 'type', 'sort_order', 'created_at', 'transaction_count'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'sort_order';
        }
        
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';

        // Add transaction count for sorting if requested
        if ($sortBy === 'transaction_count') {
            $query->withCount(['transactions' => function ($q) use ($user) {
                $q->where('user_id', $user->id);
            }])->orderBy('transactions_count', $sortDirection);
        } else {
            $query->orderBy($sortBy, $sortDirection);
        }

        // Get categories with pagination
        $categories = $query->paginate(20)->withQueryString();

        // Load transaction counts and spending totals for each category
        $categoryIds = $categories->pluck('id');
        
        $categoryStats = DB::table('transactions')
            ->select(
                'category_id',
                DB::raw('COUNT(*) as transaction_count'),
                DB::raw('SUM(CASE WHEN type = "expense" THEN amount ELSE 0 END) as total_expenses'),
                DB::raw('SUM(CASE WHEN type = "income" THEN amount ELSE 0 END) as total_income'),
                DB::raw('AVG(amount) as avg_amount'),
                DB::raw('MAX(transaction_date) as last_used')
            )
            ->where('user_id', $user->id)
            ->whereIn('category_id', $categoryIds)
            ->groupBy('category_id')
            ->get()
            ->keyBy('category_id');

        // Attach stats to categories
        foreach ($categories as $category) {
            $stats = $categoryStats->get($category->id);
            $category->transaction_count = $stats->transaction_count ?? 0;
            $category->total_expenses = $stats->total_expenses ?? 0;
            $category->total_income = $stats->total_income ?? 0;
            $category->avg_amount = $stats->avg_amount ?? 0;
            $category->last_used = $stats->last_used;
        }

        // Calculate summary statistics
        $categorySummary = [
            'total_categories' => Category::forUser($user->id)->count(),
            'custom_categories' => Category::where('user_id', $user->id)->count(),
            'active_categories' => Category::forUser($user->id)->where('is_active', true)->count(),
            'expense_categories' => Category::forUser($user->id)->where('type', 'expense')->count(),
            'income_categories' => Category::forUser($user->id)->where('type', 'income')->count(),
        ];

        return view('categories.index', compact(
            'categories',
            'categorySummary',
            'request'
        ));
    }

    /**
     * Show the form for creating a new category.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        $user = Auth::user();

        // Get parent categories (for hierarchical structure)
        $parentCategories = Category::forUser($user->id)
            ->where('is_active', true)
            ->whereNull('parent_id') // Only top-level categories can be parents
            ->orderBy('type')
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'color']);

        // Get available colors for the category
        $availableColors = Category::getAvailableColors();

        // Get available icons
        $availableIcons = Category::getAvailableIcons();

        return view('categories.create', compact(
            'parentCategories',
            'availableColors',
            'availableIcons'
        ));
    }

    /**
     * Store a newly created category in storage.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                // Ensure category name is unique for this user and type
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::id())
                                 ->where('type', $request->type);
                }),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense']),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($request) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })
                    ->where('is_active', true)
                    ->where('type', $request->type) // Parent must be same type
                    ->whereNull('parent_id'); // Parents cannot have parents (2-level limit)
                }),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[a-fA-F0-9]{6}$/', // Valid hex color code
                Rule::in(Category::getAvailableColors()),
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(Category::getAvailableIcons()),
            ],
            'is_active' => [
                'boolean',
            ],
            'is_tax_related' => [
                'boolean',
            ],
        ]);

        DB::beginTransaction();

        try {
            // Create the category
            $category = new Category();
            $category->user_id = Auth::id();
            $category->name = $validated['name'];
            $category->description = $validated['description'] ?? null;
            $category->type = $validated['type'];
            $category->parent_id = $validated['parent_id'] ?? null;
            $category->color = $validated['color'] ?? $this->getDefaultColorForType($validated['type']);
            $category->icon = $validated['icon'] ?? $this->getDefaultIconForType($validated['type']);
            $category->is_active = $validated['is_active'] ?? true;
            $category->is_tax_related = $validated['is_tax_related'] ?? false;
            $category->is_system = false; // User-created categories are never system categories
            
            // Set sort order (put new categories at the end)
            $maxSortOrder = Category::forUser(Auth::id())
                ->where('type', $validated['type'])
                ->max('sort_order') ?? 0;
            $category->sort_order = $maxSortOrder + 1;

            $category->save();

            // Log the category creation
            \Log::info('Category created', [
                'user_id' => Auth::id(),
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_type' => $category->type,
                'parent_id' => $category->parent_id,
            ]);

            DB::commit();

            return redirect()
                ->route('categories.show', $category)
                ->with('success', 'Category created successfully! You can now use it to organize your transactions.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Category creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->all(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create category. Please try again.']);
        }
    }

    /**
     * Display the specified category.
     * 
     * @param Category $category
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Category $category)
    {
        // Ensure the user can view this category
        $this->authorize('view', $category);

        $user = Auth::user();

        // Get recent transactions for this category
        $recentTransactions = $user->transactions()
            ->with(['account:id,name,color'])
            ->where('category_id', $category->id)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate category statistics
        $stats = [
            'total_transactions' => $user->transactions()->where('category_id', $category->id)->count(),
            'total_amount' => $user->transactions()->where('category_id', $category->id)->sum('amount'),
            'avg_amount' => $user->transactions()->where('category_id', $category->id)->avg('amount') ?? 0,
            'max_amount' => $user->transactions()->where('category_id', $category->id)->max('amount') ?? 0,
            'min_amount' => $user->transactions()->where('category_id', $category->id)->min('amount') ?? 0,
            'first_used' => $user->transactions()->where('category_id', $category->id)->min('transaction_date'),
            'last_used' => $user->transactions()->where('category_id', $category->id)->max('transaction_date'),
        ];

        // Get monthly spending trends for this category (last 12 months)
        $monthlyTrends = $user->transactions()
            ->selectRaw('
                YEAR(transaction_date) as year,
                MONTH(transaction_date) as month,
                SUM(amount) as total_amount,
                COUNT(*) as transaction_count
            ')
            ->where('category_id', $category->id)
            ->where('transaction_date', '>=', now()->subMonths(12))
            ->groupBy('year', 'month')
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Get subcategories if this is a parent category
        $subcategories = collect();
        if (!$category->parent_id) {
            $subcategories = Category::forUser($user->id)
                ->where('parent_id', $category->id)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        }

        // Get budget information if category is used in budgets
        $budgetInfo = $user->budgets()
            ->where('status', 'active')
            ->whereHas('budgetCategories', function ($query) use ($category) {
                $query->where('category_id', $category->id);
            })
            ->with(['budgetCategories' => function ($query) use ($category) {
                $query->where('category_id', $category->id);
            }])
            ->first();

        return view('categories.show', compact(
            'category',
            'recentTransactions',
            'stats',
            'monthlyTrends',
            'subcategories',
            'budgetInfo'
        ));
    }

    /**
     * Show the form for editing the specified category.
     * 
     * @param Category $category
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Category $category)
    {
        // Ensure the user can edit this category
        $this->authorize('update', $category);

        $user = Auth::user();

        // Get parent categories (excluding this category and its children)
        $parentCategories = Category::forUser($user->id)
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->where('type', $category->type)
            ->orderBy('name')
            ->get(['id', 'name', 'type', 'color']);

        // Get available colors and icons
        $availableColors = Category::getAvailableColors();
        $availableIcons = Category::getAvailableIcons();

        // Check if category can be safely deleted
        $canDelete = $this->canDeleteCategory($category);

        return view('categories.edit', compact(
            'category',
            'parentCategories',
            'availableColors',
            'availableIcons',
            'canDelete'
        ));
    }

    /**
     * Update the specified category in storage.
     * 
     * @param Request $request
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Category $category)
    {
        // Ensure the user can edit this category
        $this->authorize('update', $category);

        // Validate the request data
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                // Unique name for user and type, excluding current category
                Rule::unique('categories')->where(function ($query) use ($request) {
                    return $query->where('user_id', Auth::id())
                                 ->where('type', $request->type);
                })->ignore($category->id),
            ],
            'description' => [
                'nullable',
                'string',
                'max:500',
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) use ($request, $category) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })
                    ->where('is_active', true)
                    ->where('type', $category->type)
                    ->whereNull('parent_id')
                    ->where('id', '!=', $category->id); // Cannot be parent of itself
                }),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[a-fA-F0-9]{6}$/',
                Rule::in(Category::getAvailableColors()),
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
                Rule::in(Category::getAvailableIcons()),
            ],
            'is_active' => [
                'boolean',
            ],
            'is_tax_related' => [
                'boolean',
            ],
        ]);

        // Check if deactivating a category that's used in active budgets
        if (!$validated['is_active'] && $category->is_active) {
            $activeBudgetCount = Auth::user()->budgets()
                ->where('status', 'active')
                ->whereHas('budgetCategories', function ($query) use ($category) {
                    $query->where('category_id', $category->id);
                })
                ->count();

            if ($activeBudgetCount > 0) {
                return back()
                    ->withInput()
                    ->withErrors([
                        'is_active' => "Cannot deactivate category as it's used in {$activeBudgetCount} active budget(s)."
                    ]);
            }
        }

        DB::beginTransaction();

        try {
            // Update the category
            $category->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'parent_id' => $validated['parent_id'],
                'color' => $validated['color'],
                'icon' => $validated['icon'],
                'is_active' => $validated['is_active'] ?? true,
                'is_tax_related' => $validated['is_tax_related'] ?? false,
            ]);

            // Log the category update
            \Log::info('Category updated', [
                'user_id' => Auth::id(),
                'category_id' => $category->id,
                'changes' => $category->getChanges(),
            ]);

            DB::commit();

            return redirect()
                ->route('categories.show', $category)
                ->with('success', 'Category updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Category update failed', [
                'user_id' => Auth::id(),
                'category_id' => $category->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update category. Please try again.']);
        }
    }

    /**
     * Remove the specified category from storage.
     * 
     * @param Category $category
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Category $category)
    {
        // Ensure the user can delete this category
        $this->authorize('delete', $category);

        // Check if category can be safely deleted
        if (!$this->canDeleteCategory($category)) {
            return back()->withErrors([
                'general' => 'Cannot delete this category because it has associated transactions or is used in budgets.'
            ]);
        }

        DB::beginTransaction();

        try {
            // Log the category deletion
            \Log::info('Category deleted', [
                'user_id' => Auth::id(),
                'category_id' => $category->id,
                'category_name' => $category->name,
                'category_type' => $category->type,
            ]);

            // Delete the category
            $category->delete();

            DB::commit();

            return redirect()
                ->route('categories.index')
                ->with('success', "Category '{$category->name}' has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Category deletion failed', [
                'user_id' => Auth::id(),
                'category_id' => $category->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Failed to delete category. Please try again.'
            ]);
        }
    }

    /**
     * Update the sort order of categories.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'required|integer|exists:categories,id',
        ]);

        $user = Auth::user();

        DB::beginTransaction();

        try {
            foreach ($request->categories as $index => $categoryId) {
                Category::where('id', $categoryId)
                    ->where(function ($query) use ($user) {
                        $query->where('user_id', $user->id)
                              ->orWhere('is_system', true);
                    })
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Category order updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update category order.',
            ], 500);
        }
    }

    /**
     * Check if a category can be safely deleted.
     * 
     * @param Category $category
     * @return bool
     */
    private function canDeleteCategory(Category $category)
    {
        $user = Auth::user();

        // System categories cannot be deleted
        if ($category->is_system) {
            return false;
        }

        // Categories with transactions cannot be deleted
        if ($user->transactions()->where('category_id', $category->id)->exists()) {
            return false;
        }

        // Categories used in budgets cannot be deleted
        if ($user->budgets()->whereHas('budgetCategories', function ($query) use ($category) {
            $query->where('category_id', $category->id);
        })->exists()) {
            return false;
        }

        // Categories with subcategories cannot be deleted
        if (Category::where('parent_id', $category->id)->exists()) {
            return false;
        }

        return true;
    }

    /**
     * Get default color for category type.
     * 
     * @param string $type
     * @return string
     */
    private function getDefaultColorForType(string $type)
    {
        return $type === 'income' ? '#10b981' : '#ef4444';
    }

    /**
     * Get default icon for category type.
     * 
     * @param string $type
     * @return string
     */
    private function getDefaultIconForType(string $type)
    {
        return $type === 'income' ? 'currency-dollar' : 'shopping-cart';
    }

    /**
     * Get categories for API calls.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $type = $request->get('type'); // income or expense

        $query = Category::forUser($user->id)->active();
        
        if ($type) {
            $query->where('type', $type);
        }

        $categories = $query->orderBy('type')->orderBy('sort_order')->get([
            'id', 'name', 'type', 'color', 'icon', 'is_system'
        ]);

        return response()->json(['categories' => $categories]);
    }
}