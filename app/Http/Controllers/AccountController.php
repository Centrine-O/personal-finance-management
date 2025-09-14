<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

/**
 * Account Controller
 * 
 * This controller handles all CRUD operations for financial accounts.
 * Accounts are the foundation of personal finance management - they represent
 * bank accounts, credit cards, loans, investments, and other financial accounts.
 * 
 * Key Features:
 * - Account creation with validation and security checks
 * - Account listing with filtering and sorting
 * - Account editing with balance reconciliation
 * - Account deletion with transaction handling
 * - Account balance management and calculations
 * - Account type categorization (checking, savings, credit, etc.)
 * - Security measures to ensure users only access their own accounts
 * 
 * Why accounts are important:
 * - They track where money is stored (banks, wallets, investments)
 * - They enable transaction categorization by source/destination
 * - They provide balance tracking and reconciliation
 * - They support multiple currencies and account types
 * - They enable comprehensive financial reporting
 */
class AccountController extends Controller
{
    /**
     * Create a new controller instance.
     * 
     * Apply middleware to ensure proper authentication and authorization.
     * Financial account access requires the highest level of security.
     */
    public function __construct()
    {
        // Require authentication for all account operations
        $this->middleware('auth');
        
        // Require email verification for financial data access
        $this->middleware('verified');
        
        // Check account status (active, not suspended, not locked)
        $this->middleware('check.account.status');
        
        // Apply rate limiting for account creation to prevent abuse
        $this->middleware('throttle:10,1')->only(['store', 'update']);
    }

    /**
     * Display a listing of the user's accounts.
     * 
     * This method shows all accounts belonging to the authenticated user
     * with filtering, sorting, and search capabilities.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Start with the user's accounts query
        $query = $user->accounts();

        // Apply search filter if provided
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('institution_name', 'like', "%{$searchTerm}%")
                  ->orWhere('account_number_last4', 'like', "%{$searchTerm}%");
            });
        }

        // Apply account type filter if provided
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Apply status filter (active/inactive)
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'sort_order');
        $sortDirection = $request->get('direction', 'asc');
        
        // Validate sort parameters to prevent SQL injection
        $allowedSorts = ['name', 'type', 'balance', 'sort_order', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'sort_order';
        }
        
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'asc';
        
        $query->orderBy($sortBy, $sortDirection);

        // Get the accounts with pagination
        $accounts = $query->paginate(20)->withQueryString();

        // Calculate summary statistics
        $accountSummary = [
            'total_accounts' => $user->accounts()->count(),
            'active_accounts' => $user->activeAccounts()->count(),
            'total_balance' => $user->activeAccounts()->sum('balance'),
            'net_worth' => $user->calculateNetWorth(),
        ];

        // Group accounts by type for better visualization
        $accountsByType = $user->activeAccounts()
            ->get()
            ->groupBy('type')
            ->map(function ($accounts) {
                return [
                    'count' => $accounts->count(),
                    'total_balance' => $accounts->sum('balance'),
                ];
            });

        // Get available account types for the filter dropdown
        $accountTypes = Account::getAccountTypes();

        return view('accounts.index', compact(
            'accounts',
            'accountSummary',
            'accountsByType',
            'accountTypes',
            'request'
        ));
    }

    /**
     * Show the form for creating a new account.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create()
    {
        // Get available account types
        $accountTypes = Account::getAccountTypes();
        
        // Get supported currencies
        $currencies = Account::getSupportedCurrencies();
        
        // Get user's preferred currency as default
        $defaultCurrency = Auth::user()->preferred_currency ?? 'USD';

        return view('accounts.create', compact(
            'accountTypes',
            'currencies',
            'defaultCurrency'
        ));
    }

    /**
     * Store a newly created account in storage.
     * 
     * This method handles account creation with comprehensive validation
     * and security checks to ensure data integrity.
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
                // Ensure account name is unique for this user
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                }),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(array_keys(Account::getAccountTypes())),
            ],
            'institution_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'account_number' => [
                'nullable',
                'string',
                'max:50',
                // Only allow alphanumeric characters, spaces, and hyphens
                'regex:/^[a-zA-Z0-9\s\-]+$/',
            ],
            'routing_number' => [
                'nullable',
                'string',
                'size:9',
                'regex:/^[0-9]{9}$/', // Must be exactly 9 digits
            ],
            'balance' => [
                'required',
                'numeric',
                'decimal:0,2', // Maximum 2 decimal places
                'min:-999999.99',
                'max:999999.99',
            ],
            'credit_limit' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:999999.99',
                // Only allow credit limit for credit accounts
                Rule::requiredIf(function () use ($request) {
                    return $request->type === 'credit';
                }),
            ],
            'interest_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,4', // Up to 4 decimal places for precision
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                Rule::in(array_keys(Account::getSupportedCurrencies())),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[a-fA-F0-9]{6}$/', // Valid hex color code
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'boolean',
            ],
        ]);

        // Use database transaction to ensure data consistency
        DB::beginTransaction();

        try {
            // Create the account
            $account = new Account();
            $account->user_id = Auth::id();
            $account->name = $validated['name'];
            $account->type = $validated['type'];
            $account->institution_name = $validated['institution_name'] ?? null;
            
            // Handle account number securely
            if (!empty($validated['account_number'])) {
                // Store only the last 4 digits for display
                $account->account_number_last4 = substr($validated['account_number'], -4);
                // In a real application, you would encrypt the full account number
                // $account->account_number_encrypted = encrypt($validated['account_number']);
            }
            
            $account->routing_number = $validated['routing_number'] ?? null;
            $account->balance = $validated['balance'];
            $account->credit_limit = $validated['credit_limit'] ?? null;
            $account->interest_rate = $validated['interest_rate'] ?? null;
            $account->currency = $validated['currency'];
            $account->color = $validated['color'] ?? Account::getDefaultColorForType($validated['type']);
            $account->icon = $validated['icon'] ?? Account::getDefaultIconForType($validated['type']);
            $account->notes = $validated['notes'] ?? null;
            $account->is_active = $validated['is_active'] ?? true;
            
            // Set sort order (put new accounts at the end)
            $account->sort_order = Auth::user()->accounts()->max('sort_order') + 1;

            $account->save();

            // Log the account creation for security auditing
            \Log::info('Account created', [
                'user_id' => Auth::id(),
                'account_id' => $account->id,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'initial_balance' => $account->balance,
                'ip_address' => $request->ip(),
            ]);

            DB::commit();

            return redirect()
                ->route('accounts.show', $account)
                ->with('success', 'Account created successfully! You can now start adding transactions.');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Account creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['account_number']), // Don't log sensitive data
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create account. Please try again.']);
        }
    }

    /**
     * Display the specified account.
     * 
     * Shows detailed account information including recent transactions,
     * balance history, and account statistics.
     * 
     * @param Account $account
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Account $account)
    {
        // Ensure the user owns this account
        $this->authorize('view', $account);

        // Get recent transactions for this account
        $recentTransactions = $account->transactions()
            ->with(['category:id,name,color'])
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Calculate account statistics
        $stats = [
            'total_transactions' => $account->transactions()->count(),
            'transactions_this_month' => $account->transactions()
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->count(),
            'average_transaction' => $account->transactions()->avg('amount') ?? 0,
            'largest_transaction' => $account->transactions()->max('amount') ?? 0,
            'income_this_month' => $account->transactions()
                ->where('type', 'income')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
            'expenses_this_month' => $account->transactions()
                ->where('type', 'expense')
                ->whereYear('transaction_date', now()->year)
                ->whereMonth('transaction_date', now()->month)
                ->sum('amount'),
        ];

        // Calculate balance trend (simplified - in real app you'd track daily balances)
        $balanceHistory = $account->getBalanceHistory(30); // Last 30 days

        return view('accounts.show', compact(
            'account',
            'recentTransactions',
            'stats',
            'balanceHistory'
        ));
    }

    /**
     * Show the form for editing the specified account.
     * 
     * @param Account $account
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Account $account)
    {
        // Ensure the user owns this account
        $this->authorize('update', $account);

        // Get available account types
        $accountTypes = Account::getAccountTypes();
        
        // Get supported currencies
        $currencies = Account::getSupportedCurrencies();

        return view('accounts.edit', compact(
            'account',
            'accountTypes',
            'currencies'
        ));
    }

    /**
     * Update the specified account in storage.
     * 
     * @param Request $request
     * @param Account $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Account $account)
    {
        // Ensure the user owns this account
        $this->authorize('update', $account);

        // Validate the request data
        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                'min:2',
                // Ensure account name is unique for this user (except current account)
                Rule::unique('accounts')->where(function ($query) {
                    return $query->where('user_id', Auth::id());
                })->ignore($account->id),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(array_keys(Account::getAccountTypes())),
            ],
            'institution_name' => [
                'nullable',
                'string',
                'max:255',
            ],
            'balance' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:-999999.99',
                'max:999999.99',
            ],
            'credit_limit' => [
                'nullable',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:999999.99',
                Rule::requiredIf(function () use ($request) {
                    return $request->type === 'credit';
                }),
            ],
            'interest_rate' => [
                'nullable',
                'numeric',
                'min:0',
                'max:100',
                'decimal:0,4',
            ],
            'currency' => [
                'required',
                'string',
                'size:3',
                Rule::in(array_keys(Account::getSupportedCurrencies())),
            ],
            'color' => [
                'nullable',
                'string',
                'regex:/^#[a-fA-F0-9]{6}$/',
            ],
            'icon' => [
                'nullable',
                'string',
                'max:50',
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'is_active' => [
                'boolean',
            ],
        ]);

        // Use database transaction
        DB::beginTransaction();

        try {
            // Store original balance for logging
            $originalBalance = $account->balance;

            // Update the account
            $account->update([
                'name' => $validated['name'],
                'type' => $validated['type'],
                'institution_name' => $validated['institution_name'],
                'balance' => $validated['balance'],
                'credit_limit' => $validated['credit_limit'],
                'interest_rate' => $validated['interest_rate'],
                'currency' => $validated['currency'],
                'color' => $validated['color'],
                'icon' => $validated['icon'],
                'notes' => $validated['notes'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // If balance changed, log it for audit purposes
            if ($originalBalance != $validated['balance']) {
                \Log::info('Account balance manually adjusted', [
                    'user_id' => Auth::id(),
                    'account_id' => $account->id,
                    'old_balance' => $originalBalance,
                    'new_balance' => $validated['balance'],
                    'difference' => $validated['balance'] - $originalBalance,
                    'ip_address' => $request->ip(),
                ]);
            }

            DB::commit();

            return redirect()
                ->route('accounts.show', $account)
                ->with('success', 'Account updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Account update failed', [
                'user_id' => Auth::id(),
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update account. Please try again.']);
        }
    }

    /**
     * Remove the specified account from storage.
     * 
     * This method handles account deletion safely, ensuring that
     * associated transactions are handled appropriately.
     * 
     * @param Account $account
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Account $account)
    {
        // Ensure the user owns this account
        $this->authorize('delete', $account);

        // Check if account has transactions
        $transactionCount = $account->transactions()->count();
        
        if ($transactionCount > 0) {
            return back()->withErrors([
                'general' => "Cannot delete account '{$account->name}' because it has {$transactionCount} transactions. Please transfer or delete the transactions first."
            ]);
        }

        DB::beginTransaction();

        try {
            // Log the account deletion
            \Log::info('Account deleted', [
                'user_id' => Auth::id(),
                'account_id' => $account->id,
                'account_name' => $account->name,
                'account_type' => $account->type,
                'final_balance' => $account->balance,
            ]);

            // Delete the account
            $account->delete();

            DB::commit();

            return redirect()
                ->route('accounts.index')
                ->with('success', "Account '{$account->name}' has been deleted successfully.");

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Account deletion failed', [
                'user_id' => Auth::id(),
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Failed to delete account. Please try again.'
            ]);
        }
    }

    /**
     * Update the sort order of accounts.
     * 
     * This method allows users to reorder their accounts for better organization.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Request $request)
    {
        $request->validate([
            'accounts' => 'required|array',
            'accounts.*' => 'required|integer|exists:accounts,id',
        ]);

        $user = Auth::user();

        DB::beginTransaction();

        try {
            foreach ($request->accounts as $index => $accountId) {
                $user->accounts()
                    ->where('id', $accountId)
                    ->update(['sort_order' => $index + 1]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Account order updated successfully.',
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update account order.',
            ], 500);
        }
    }

    /**
     * Get account data for API calls.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        
        $accounts = $user->activeAccounts()
            ->orderBy('sort_order')
            ->get()
            ->map(function ($account) {
                return [
                    'id' => $account->id,
                    'name' => $account->name,
                    'type' => $account->type,
                    'balance' => $account->balance,
                    'formatted_balance' => $account->formatted_balance,
                    'currency' => $account->currency,
                    'color' => $account->color,
                    'icon' => $account->icon,
                    'is_asset' => $account->is_asset,
                ];
            });

        return response()->json([
            'accounts' => $accounts,
            'summary' => [
                'total_accounts' => $accounts->count(),
                'net_worth' => $user->calculateNetWorth(),
                'total_assets' => $accounts->where('is_asset', true)->sum('balance'),
                'total_liabilities' => abs($accounts->where('is_asset', false)->sum('balance')),
            ],
        ]);
    }
}