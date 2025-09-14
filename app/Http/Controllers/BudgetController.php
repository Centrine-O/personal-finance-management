<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Models\BudgetCategory;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Budget Controller
 * 
 * This controller handles all CRUD operations for budgets and budget management.
 * Budgets are essential for financial planning and expense control - they help
 * users set spending limits, track progress, and make informed financial decisions.
 * 
 * Key Features:
 * - Budget creation with category allocations
 * - Budget progress tracking and analytics
 * - Budget comparison and historical analysis
 * - Overspending alerts and notifications
 * - Budget templates for quick setup
 * - Multi-period budget management
 * - Budget vs actual reporting
 * - Category-wise budget breakdown
 * - Automatic budget rollover options
 * - Budget sharing and collaboration
 * 
 * Why budgets are crucial:
 * - They provide spending guidelines and financial discipline
 * - They help achieve financial goals through planned allocation
 * - They enable early detection of overspending patterns
 * - They facilitate better financial decision-making
 * - They support long-term financial planning and stability
 */
class BudgetController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Require authentication for all budget operations
        $this->middleware('auth');
        
        // Require email verification for financial data access
        $this->middleware('verified');
        
        // Check account status (active, not suspended, not locked)
        $this->middleware('check.account.status');
        
        // Apply rate limiting for budget operations
        $this->middleware('throttle:30,1')->only(['store', 'update']);
    }

    /**
     * Display a listing of the user's budgets.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Get all budgets with filtering
        $query = $user->budgets()->with('budgetCategories.category');

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('status', 'active');
            } elseif ($request->status === 'completed') {
                $query->where('status', 'completed');
            } elseif ($request->status === 'paused') {
                $query->where('status', 'paused');
            }
        }

        // Apply date range filter
        if ($request->filled('year')) {
            $query->whereYear('start_date', $request->year);
        }

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'start_date');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'start_date', 'end_date', 'planned_income', 'planned_expenses', 'created_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'start_date';
        }
        
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';
        $query->orderBy($sortBy, $sortDirection);

        // Get budgets with pagination
        $budgets = $query->paginate(12)->withQueryString();

        // Calculate summary statistics
        $budgetSummary = [
            'total_budgets' => $user->budgets()->count(),
            'active_budgets' => $user->budgets()->where('status', 'active')->count(),
            'current_budget' => $user->activeBudget(),
        ];

        // Get available years for filter
        $availableYears = $user->budgets()
            ->selectRaw('YEAR(start_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');

        return view('budgets.index', compact(
            'budgets',
            'budgetSummary',
            'availableYears',
            'request'
        ));
    }

    /**
     * Show the form for creating a new budget.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get active categories for budget allocation
        $categories = Category::forUser($user->id)
            ->active()
            ->where('type', 'expense') // Only expense categories for budgeting
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        // Get recent budget for template suggestion
        $recentBudget = $user->budgets()
            ->with('budgetCategories.category')
            ->orderBy('created_at', 'desc')
            ->first();

        // Suggest default budget period (current month)
        $defaultStartDate = now()->startOfMonth()->toDateString();
        $defaultEndDate = now()->endOfMonth()->toDateString();

        // Calculate suggested amounts based on recent transactions
        $suggestedAmounts = $this->calculateSuggestedBudgetAmounts($user);

        return view('budgets.create', compact(
            'categories',
            'recentBudget',
            'defaultStartDate',
            'defaultEndDate',
            'suggestedAmounts'
        ));
    }

    /**
     * Store a newly created budget in storage.
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
                'min:3',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:' . now()->subYear()->toDateString(),
                'before_or_equal:' . now()->addYear()->toDateString(),
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
                'before_or_equal:' . now()->addYears(2)->toDateString(),
            ],
            'planned_income' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],
            'planned_expenses' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],
            'budget_categories' => [
                'required',
                'array',
                'min:1',
            ],
            'budget_categories.*.category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })->where('is_active', true)
                      ->where('type', 'expense');
                }),
            ],
            'budget_categories.*.allocated_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
            'budget_categories.*.notes' => [
                'nullable',
                'string',
                'max:500',
            ],
            'auto_rollover' => [
                'boolean',
            ],
            'alert_threshold' => [
                'nullable',
                'integer',
                'min:50',
                'max:100',
            ],
        ]);

        // Validate that category allocations don't exceed planned expenses
        $totalAllocated = collect($validated['budget_categories'])->sum('allocated_amount');
        if ($totalAllocated > $validated['planned_expenses']) {
            return back()
                ->withInput()
                ->withErrors([
                    'budget_categories' => "Total category allocations ({$totalAllocated}) cannot exceed planned expenses ({$validated['planned_expenses']})."
                ]);
        }

        // Check for overlapping budgets
        $overlappingBudget = Auth::user()->budgets()
            ->where('status', 'active')
            ->where(function ($query) use ($validated) {
                $query->whereBetween('start_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhereBetween('end_date', [$validated['start_date'], $validated['end_date']])
                      ->orWhere(function ($q) use ($validated) {
                          $q->where('start_date', '<=', $validated['start_date'])
                            ->where('end_date', '>=', $validated['end_date']);
                      });
            })
            ->first();

        if ($overlappingBudget) {
            return back()
                ->withInput()
                ->withErrors([
                    'start_date' => "This budget period overlaps with existing budget: {$overlappingBudget->name}"
                ]);
        }

        DB::beginTransaction();

        try {
            // Create the budget
            $budget = new Budget();
            $budget->user_id = Auth::id();
            $budget->name = $validated['name'];
            $budget->description = $validated['description'];
            $budget->start_date = $validated['start_date'];
            $budget->end_date = $validated['end_date'];
            $budget->planned_income = $validated['planned_income'];
            $budget->planned_expenses = $validated['planned_expenses'];
            $budget->status = 'active';
            $budget->auto_rollover = $validated['auto_rollover'] ?? false;
            $budget->alert_threshold = $validated['alert_threshold'] ?? 80;

            $budget->save();

            // Create budget categories
            foreach ($validated['budget_categories'] as $categoryData) {
                $budgetCategory = new BudgetCategory();
                $budgetCategory->budget_id = $budget->id;
                $budgetCategory->category_id = $categoryData['category_id'];
                $budgetCategory->allocated_amount = $categoryData['allocated_amount'];
                $budgetCategory->notes = $categoryData['notes'] ?? null;

                $budgetCategory->save();
            }

            // Log the budget creation
            \Log::info('Budget created', [
                'user_id' => Auth::id(),
                'budget_id' => $budget->id,
                'budget_name' => $budget->name,
                'period' => $budget->start_date . ' to ' . $budget->end_date,
                'planned_income' => $budget->planned_income,
                'planned_expenses' => $budget->planned_expenses,
                'categories_count' => count($validated['budget_categories']),
            ]);

            DB::commit();

            return redirect()
                ->route('budgets.show', $budget)
                ->with('success', 'Budget created successfully! Start tracking your expenses against your plan.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Budget creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['budget_categories']),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create budget. Please try again.']);
        }
    }

    /**
     * Display the specified budget.
     * 
     * @param Budget $budget
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Budget $budget)
    {
        // Ensure the user owns this budget
        $this->authorize('view', $budget);

        // Load related data
        $budget->load('budgetCategories.category');

        // Calculate current progress
        $budget->calculateActualAmounts();

        // Get category performance data
        $categoryPerformance = $budget->budgetCategories->map(function ($budgetCategory) {
            $spent = $budgetCategory->spent_amount;
            $allocated = $budgetCategory->allocated_amount;
            $remaining = $allocated - $spent;
            $usagePercentage = $allocated > 0 ? ($spent / $allocated) * 100 : 0;

            return [
                'category' => $budgetCategory->category,
                'allocated' => $allocated,
                'spent' => $spent,
                'remaining' => $remaining,
                'usage_percentage' => $usagePercentage,
                'status' => $this->getCategoryBudgetStatus($usagePercentage, $budget->alert_threshold),
                'is_overspent' => $spent > $allocated,
                'notes' => $budgetCategory->notes,
            ];
        })->sortByDesc('usage_percentage');

        // Calculate overall budget health
        $budgetHealth = [
            'income_progress' => $budget->planned_income > 0 
                ? ($budget->actual_income / $budget->planned_income) * 100 
                : 0,
            'expense_progress' => $budget->planned_expenses > 0 
                ? ($budget->actual_expenses / $budget->planned_expenses) * 100 
                : 0,
            'days_elapsed' => now()->diffInDays($budget->start_date),
            'days_total' => $budget->end_date->diffInDays($budget->start_date),
            'time_progress' => 0,
        ];

        // Calculate time progress
        if ($budgetHealth['days_total'] > 0) {
            $budgetHealth['time_progress'] = min(
                ($budgetHealth['days_elapsed'] / $budgetHealth['days_total']) * 100, 
                100
            );
        }

        // Get spending trends
        $spendingTrends = $this->getSpendingTrends($budget);

        // Get budget insights and recommendations
        $insights = $this->generateBudgetInsights($budget, $categoryPerformance, $budgetHealth);

        return view('budgets.show', compact(
            'budget',
            'categoryPerformance',
            'budgetHealth',
            'spendingTrends',
            'insights'
        ));
    }

    /**
     * Show the form for editing the specified budget.
     * 
     * @param Budget $budget
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Budget $budget)
    {
        // Ensure the user owns this budget
        $this->authorize('update', $budget);

        // Load related data
        $budget->load('budgetCategories.category');

        // Get active categories
        $categories = Category::forUser(Auth::id())
            ->active()
            ->where('type', 'expense')
            ->orderBy('name')
            ->get(['id', 'name', 'color']);

        return view('budgets.edit', compact(
            'budget',
            'categories'
        ));
    }

    /**
     * Update the specified budget in storage.
     * 
     * @param Request $request
     * @param Budget $budget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Budget $budget)
    {
        // Ensure the user owns this budget
        $this->authorize('update', $budget);

        // Validate the request data (same as store method)
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'planned_income' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],
            'planned_expenses' => [
                'required',
                'numeric',
                'min:0',
                'max:999999.99',
                'decimal:0,2',
            ],
            'budget_categories' => [
                'required',
                'array',
                'min:1',
            ],
            'budget_categories.*.category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })->where('is_active', true)
                      ->where('type', 'expense');
                }),
            ],
            'budget_categories.*.allocated_amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
            'budget_categories.*.notes' => [
                'nullable',
                'string',
                'max:500',
            ],
            'alert_threshold' => [
                'nullable',
                'integer',
                'min:50',
                'max:100',
            ],
        ]);

        // Validate total allocations
        $totalAllocated = collect($validated['budget_categories'])->sum('allocated_amount');
        if ($totalAllocated > $validated['planned_expenses']) {
            return back()
                ->withInput()
                ->withErrors([
                    'budget_categories' => "Total category allocations cannot exceed planned expenses."
                ]);
        }

        DB::beginTransaction();

        try {
            // Update the budget
            $budget->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'planned_income' => $validated['planned_income'],
                'planned_expenses' => $validated['planned_expenses'],
                'alert_threshold' => $validated['alert_threshold'] ?? 80,
            ]);

            // Delete existing budget categories
            $budget->budgetCategories()->delete();

            // Create new budget categories
            foreach ($validated['budget_categories'] as $categoryData) {
                $budgetCategory = new BudgetCategory();
                $budgetCategory->budget_id = $budget->id;
                $budgetCategory->category_id = $categoryData['category_id'];
                $budgetCategory->allocated_amount = $categoryData['allocated_amount'];
                $budgetCategory->notes = $categoryData['notes'] ?? null;

                $budgetCategory->save();
            }

            // Log the budget update
            \Log::info('Budget updated', [
                'user_id' => Auth::id(),
                'budget_id' => $budget->id,
                'changes' => $budget->getChanges(),
            ]);

            DB::commit();

            return redirect()
                ->route('budgets.show', $budget)
                ->with('success', 'Budget updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Budget update failed', [
                'user_id' => Auth::id(),
                'budget_id' => $budget->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update budget. Please try again.']);
        }
    }

    /**
     * Remove the specified budget from storage.
     * 
     * @param Budget $budget
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Budget $budget)
    {
        // Ensure the user owns this budget
        $this->authorize('delete', $budget);

        DB::beginTransaction();

        try {
            // Log the budget deletion
            \Log::info('Budget deleted', [
                'user_id' => Auth::id(),
                'budget_id' => $budget->id,
                'budget_name' => $budget->name,
                'period' => $budget->start_date . ' to ' . $budget->end_date,
            ]);

            // Delete budget categories first (due to foreign key constraints)
            $budget->budgetCategories()->delete();

            // Delete the budget
            $budget->delete();

            DB::commit();

            return redirect()
                ->route('budgets.index')
                ->with('success', "Budget '{$budget->name}' has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Budget deletion failed', [
                'user_id' => Auth::id(),
                'budget_id' => $budget->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Failed to delete budget. Please try again.'
            ]);
        }
    }

    /**
     * Calculate suggested budget amounts based on historical data.
     * 
     * @param \App\Models\User $user
     * @return array
     */
    private function calculateSuggestedBudgetAmounts($user)
    {
        // Get average monthly income and expenses from last 3 months
        $threeMonthsAgo = now()->subMonths(3);
        
        $avgIncome = $user->transactions()
            ->where('type', 'income')
            ->where('transaction_date', '>=', $threeMonthsAgo)
            ->avg('amount') ?? 0;

        $categoryAverages = $user->transactions()
            ->select('category_id', DB::raw('AVG(amount) as avg_amount'))
            ->where('type', 'expense')
            ->where('transaction_date', '>=', $threeMonthsAgo)
            ->groupBy('category_id')
            ->with('category:id,name')
            ->get()
            ->pluck('avg_amount', 'category_id');

        return [
            'suggested_income' => round($avgIncome * 1.05, 2), // 5% buffer
            'category_suggestions' => $categoryAverages->map(function ($amount) {
                return round($amount * 1.1, 2); // 10% buffer for expenses
            }),
        ];
    }

    /**
     * Get category budget status based on usage percentage.
     * 
     * @param float $usagePercentage
     * @param int $alertThreshold
     * @return string
     */
    private function getCategoryBudgetStatus($usagePercentage, $alertThreshold)
    {
        if ($usagePercentage >= 100) {
            return 'overspent';
        } elseif ($usagePercentage >= $alertThreshold) {
            return 'warning';
        } elseif ($usagePercentage >= 50) {
            return 'good';
        } else {
            return 'excellent';
        }
    }

    /**
     * Get spending trends for the budget period.
     * 
     * @param Budget $budget
     * @return array
     */
    private function getSpendingTrends(Budget $budget)
    {
        // This would calculate daily/weekly spending trends
        // For now, returning placeholder data
        return [
            'daily_average' => $budget->actual_expenses / max($budget->start_date->diffInDays(now()), 1),
            'projected_total' => 0, // Would calculate based on current trend
            'pace' => 'on_track', // on_track, ahead, behind
        ];
    }

    /**
     * Generate budget insights and recommendations.
     * 
     * @param Budget $budget
     * @param \Illuminate\Support\Collection $categoryPerformance
     * @param array $budgetHealth
     * @return array
     */
    private function generateBudgetInsights(Budget $budget, $categoryPerformance, $budgetHealth)
    {
        $insights = [];

        // Check for overspending categories
        $overspentCategories = $categoryPerformance->filter(function ($performance) {
            return $performance['is_overspent'];
        });

        if ($overspentCategories->isNotEmpty()) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Overspending Alert',
                'message' => 'You have overspent in ' . $overspentCategories->count() . ' categories.',
                'action' => 'Review spending in these categories and adjust future purchases.',
            ];
        }

        // Check overall budget health
        if ($budgetHealth['expense_progress'] > $budgetHealth['time_progress'] + 10) {
            $insights[] = [
                'type' => 'warning',
                'title' => 'Ahead of Spending Pace',
                'message' => 'You are spending faster than the budget timeline suggests.',
                'action' => 'Consider reducing discretionary spending for the remainder of the period.',
            ];
        }

        // Positive reinforcement for good budget performance
        if ($budgetHealth['expense_progress'] <= $budgetHealth['time_progress'] && $overspentCategories->isEmpty()) {
            $insights[] = [
                'type' => 'success',
                'title' => 'Great Budget Discipline!',
                'message' => 'You are staying within your budget limits. Keep up the excellent work!',
                'action' => 'Continue this pattern and consider setting more ambitious savings goals.',
            ];
        }

        return $insights;
    }

    /**
     * Get budget data for API calls.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiCurrent(Request $request)
    {
        $user = Auth::user();
        $budget = $user->activeBudget();

        if (!$budget) {
            return response()->json(['budget' => null]);
        }

        $budget->load('budgetCategories.category');
        $budget->calculateActualAmounts();

        return response()->json([
            'budget' => [
                'id' => $budget->id,
                'name' => $budget->name,
                'start_date' => $budget->start_date->toDateString(),
                'end_date' => $budget->end_date->toDateString(),
                'planned_income' => $budget->planned_income,
                'actual_income' => $budget->actual_income,
                'planned_expenses' => $budget->planned_expenses,
                'actual_expenses' => $budget->actual_expenses,
                'remaining_budget' => $budget->remaining_budget,
                'is_overspent' => $budget->is_overspent,
                'categories' => $budget->budgetCategories->map(function ($budgetCategory) {
                    return [
                        'category_name' => $budgetCategory->category->name,
                        'category_color' => $budgetCategory->category->color,
                        'allocated' => $budgetCategory->allocated_amount,
                        'spent' => $budgetCategory->spent_amount,
                        'remaining' => $budgetCategory->remaining_amount,
                        'usage_percentage' => $budgetCategory->usage_percentage,
                    ];
                }),
            ],
        ]);
    }
}