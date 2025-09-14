<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Account;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

/**
 * Transaction Controller
 * 
 * This controller handles all CRUD operations for financial transactions.
 * Transactions are the core of personal finance management - they represent
 * money movement between accounts, income, expenses, and transfers.
 * 
 * Key Features:
 * - Transaction creation with automatic balance updates
 * - Transaction listing with advanced filtering and search
 * - Transaction editing with balance reconciliation
 * - Transaction deletion with proper cleanup
 * - Bulk transaction operations for efficiency
 * - Transaction categorization and tagging
 * - Split transactions for detailed tracking
 * - Transaction import/export functionality
 * - Duplicate transaction detection
 * - Advanced reporting and analytics
 * 
 * Why transactions are critical:
 * - They track all money movement and financial activity
 * - They enable budget tracking and expense analysis
 * - They provide audit trails for financial decisions
 * - They support reconciliation with bank statements
 * - They enable comprehensive financial reporting
 */
class TransactionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        // Require authentication for all transaction operations
        $this->middleware('auth');
        
        // Require email verification for financial data access
        $this->middleware('verified');
        
        // Check account status (active, not suspended, not locked)
        $this->middleware('check.account.status');
        
        // Apply rate limiting for transaction creation
        $this->middleware('throttle:100,1')->only(['store', 'update']);
    }

    /**
     * Display a listing of the user's transactions.
     * 
     * This method provides comprehensive transaction listing with advanced
     * filtering, search, and pagination capabilities.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        // Start with the user's transactions query
        $query = $user->transactions()->with(['account:id,name,color', 'category:id,name,color']);

        // Apply search filter
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('description', 'like', "%{$searchTerm}%")
                  ->orWhere('notes', 'like', "%{$searchTerm}%")
                  ->orWhere('reference_number', 'like', "%{$searchTerm}%");
            });
        }

        // Apply account filter
        if ($request->filled('account')) {
            $query->where('account_id', $request->account);
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Apply transaction type filter
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Apply amount range filter
        if ($request->filled('min_amount')) {
            $query->where('amount', '>=', $request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('amount', '<=', $request->max_amount);
        }

        // Apply date range filter
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'pending') {
                $query->where('is_pending', true);
            } elseif ($request->status === 'cleared') {
                $query->where('is_pending', false);
            }
        }

        // Apply reconciliation filter
        if ($request->filled('reconciled')) {
            if ($request->reconciled === 'yes') {
                $query->where('is_reconciled', true);
            } elseif ($request->reconciled === 'no') {
                $query->where('is_reconciled', false);
            }
        }

        // Apply sorting
        $sortBy = $request->get('sort', 'transaction_date');
        $sortDirection = $request->get('direction', 'desc');
        
        $allowedSorts = ['transaction_date', 'amount', 'description', 'created_at', 'updated_at'];
        if (!in_array($sortBy, $allowedSorts)) {
            $sortBy = 'transaction_date';
        }
        
        $sortDirection = in_array($sortDirection, ['asc', 'desc']) ? $sortDirection : 'desc';
        
        $query->orderBy($sortBy, $sortDirection);

        // Get transactions with pagination
        $transactions = $query->paginate(25)->withQueryString();

        // Calculate summary statistics for the filtered results
        $summaryQuery = clone $query;
        $summaryQuery->getQuery()->orders = null; // Remove ordering for summary
        
        $transactionSummary = [
            'total_count' => $summaryQuery->count(),
            'total_income' => $summaryQuery->where('type', 'income')->sum('amount'),
            'total_expenses' => $summaryQuery->where('type', 'expense')->sum('amount'),
            'net_amount' => $summaryQuery->selectRaw('
                SUM(CASE WHEN type = "income" THEN amount ELSE -amount END) as net
            ')->first()->net ?? 0,
            'pending_count' => $summaryQuery->where('is_pending', true)->count(),
            'unreconciled_count' => $summaryQuery->where('is_reconciled', false)->count(),
        ];

        // Get filter options
        $filterOptions = [
            'accounts' => $user->activeAccounts()->orderBy('name')->get(['id', 'name', 'color']),
            'categories' => Category::forUser($user->id)->active()->orderBy('name')->get(['id', 'name', 'color', 'type']),
            'transaction_types' => ['income', 'expense', 'transfer'],
        ];

        return view('transactions.index', compact(
            'transactions',
            'transactionSummary',
            'filterOptions',
            'request'
        ));
    }

    /**
     * Show the form for creating a new transaction.
     * 
     * @return \Illuminate\Contracts\View\View
     */
    public function create(Request $request)
    {
        $user = Auth::user();

        // Get user's active accounts
        $accounts = $user->activeAccounts()->orderBy('sort_order')->get(['id', 'name', 'color', 'type', 'currency']);
        
        // Get categories
        $categories = Category::forUser($user->id)->active()->orderBy('type')->orderBy('name')->get(['id', 'name', 'color', 'type']);
        
        // Group categories by type for better organization
        $categoriesByType = $categories->groupBy('type');

        // Pre-fill some fields if provided in query parameters
        $defaults = [
            'account_id' => $request->get('account_id'),
            'type' => $request->get('type', 'expense'),
            'transaction_date' => $request->get('date', now()->toDateString()),
            'amount' => $request->get('amount'),
            'description' => $request->get('description'),
        ];

        return view('transactions.create', compact(
            'accounts',
            'categories',
            'categoriesByType',
            'defaults'
        ));
    }

    /**
     * Store a newly created transaction in storage.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        // Validate the request data
        $validated = $request->validate([
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id())
                                 ->where('is_active', true);
                }),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })->where('is_active', true);
                }),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense', 'transfer']),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'transaction_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYears(10)->toDateString(), // Reasonable historical limit
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_pending' => [
                'boolean',
            ],
            'is_recurring' => [
                'boolean',
            ],
            // Transfer-specific fields
            'transfer_account_id' => [
                'nullable',
                'integer',
                Rule::requiredIf($request->type === 'transfer'),
                Rule::exists('accounts', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id())
                                 ->where('is_active', true);
                }),
                'different:account_id', // Cannot transfer to the same account
            ],
            // Split transaction fields
            'is_split' => [
                'boolean',
            ],
            'split_transactions' => [
                'nullable',
                'array',
                Rule::requiredIf($request->boolean('is_split')),
            ],
            'split_transactions.*.category_id' => [
                'required_with:split_transactions',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })->where('is_active', true);
                }),
            ],
            'split_transactions.*.amount' => [
                'required_with:split_transactions',
                'numeric',
                'min:0.01',
                'decimal:0,2',
            ],
            'split_transactions.*.description' => [
                'required_with:split_transactions',
                'string',
                'max:255',
            ],
        ]);

        // Additional validation for split transactions
        if ($request->boolean('is_split') && !empty($validated['split_transactions'])) {
            $splitTotal = collect($validated['split_transactions'])->sum('amount');
            if (abs($splitTotal - $validated['amount']) > 0.01) {
                return back()
                    ->withInput()
                    ->withErrors(['split_transactions' => 'Split transaction amounts must equal the total transaction amount.']);
            }
        }

        DB::beginTransaction();

        try {
            $account = Account::findOrFail($validated['account_id']);
            
            // Check if this might be a duplicate transaction
            $potentialDuplicate = $this->checkForDuplicate($validated, $account);
            if ($potentialDuplicate && !$request->boolean('ignore_duplicate')) {
                return back()
                    ->withInput()
                    ->with('potential_duplicate', $potentialDuplicate)
                    ->withErrors(['duplicate' => 'A similar transaction was found. Please confirm this is not a duplicate.']);
            }

            // Create the main transaction
            $transaction = new Transaction();
            $transaction->user_id = Auth::id();
            $transaction->account_id = $validated['account_id'];
            $transaction->category_id = $validated['category_id'];
            $transaction->type = $validated['type'];
            $transaction->amount = $validated['amount'];
            $transaction->description = $validated['description'];
            $transaction->transaction_date = $validated['transaction_date'];
            $transaction->notes = $validated['notes'] ?? null;
            $transaction->reference_number = $validated['reference_number'] ?? null;
            $transaction->is_pending = $validated['is_pending'] ?? false;
            $transaction->is_split = $validated['is_split'] ?? false;
            
            $transaction->save();

            // Handle transfer transactions
            if ($validated['type'] === 'transfer') {
                $transferAccount = Account::findOrFail($validated['transfer_account_id']);
                
                // Create the corresponding transaction in the destination account
                $transferTransaction = new Transaction();
                $transferTransaction->user_id = Auth::id();
                $transferTransaction->account_id = $validated['transfer_account_id'];
                $transferTransaction->category_id = $validated['category_id'];
                $transferTransaction->type = 'transfer';
                $transferTransaction->amount = $validated['amount'];
                $transferTransaction->description = $validated['description'] . ' (Transfer from ' . $account->name . ')';
                $transferTransaction->transaction_date = $validated['transaction_date'];
                $transferTransaction->notes = $validated['notes'] ?? null;
                $transferTransaction->reference_number = $validated['reference_number'] ?? null;
                $transferTransaction->is_pending = $validated['is_pending'] ?? false;
                $transferTransaction->transfer_transaction_id = $transaction->id;
                
                $transferTransaction->save();
                
                // Link the transactions
                $transaction->transfer_transaction_id = $transferTransaction->id;
                $transaction->save();

                // Update account balances for transfer
                if (!$transaction->is_pending) {
                    $account->decrement('balance', $validated['amount']);
                    $transferAccount->increment('balance', $validated['amount']);
                }
            } else {
                // Update account balance for income/expense
                if (!$transaction->is_pending) {
                    if ($validated['type'] === 'income') {
                        $account->increment('balance', $validated['amount']);
                    } else { // expense
                        $account->decrement('balance', $validated['amount']);
                    }
                }
            }

            // Handle split transactions
            if ($validated['is_split'] && !empty($validated['split_transactions'])) {
                foreach ($validated['split_transactions'] as $splitData) {
                    $splitTransaction = new Transaction();
                    $splitTransaction->user_id = Auth::id();
                    $splitTransaction->account_id = $validated['account_id'];
                    $splitTransaction->category_id = $splitData['category_id'];
                    $splitTransaction->type = $validated['type'];
                    $splitTransaction->amount = $splitData['amount'];
                    $splitTransaction->description = $splitData['description'];
                    $splitTransaction->transaction_date = $validated['transaction_date'];
                    $splitTransaction->notes = $validated['notes'] ?? null;
                    $splitTransaction->is_pending = $validated['is_pending'] ?? false;
                    $splitTransaction->parent_transaction_id = $transaction->id;
                    
                    $splitTransaction->save();
                }
            }

            // Log the transaction creation
            \Log::info('Transaction created', [
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
            ]);

            DB::commit();

            $message = 'Transaction added successfully!';
            if ($validated['type'] === 'transfer') {
                $message .= ' Transfer completed between accounts.';
            }

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', $message);

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Transaction creation failed', [
                'user_id' => Auth::id(),
                'error' => $e->getMessage(),
                'request_data' => $request->except(['split_transactions']),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to create transaction. Please try again.']);
        }
    }

    /**
     * Display the specified transaction.
     * 
     * @param Transaction $transaction
     * @return \Illuminate\Contracts\View\View
     */
    public function show(Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('view', $transaction);

        // Load related data
        $transaction->load(['account', 'category', 'splitTransactions.category']);

        // Get transfer transaction if this is a transfer
        $transferTransaction = null;
        if ($transaction->type === 'transfer' && $transaction->transfer_transaction_id) {
            $transferTransaction = Transaction::with(['account', 'category'])
                ->find($transaction->transfer_transaction_id);
        }

        // Get similar transactions for insights
        $similarTransactions = Transaction::where('user_id', Auth::id())
            ->where('id', '!=', $transaction->id)
            ->where(function ($query) use ($transaction) {
                $query->where('description', 'like', '%' . $transaction->description . '%')
                      ->orWhere('category_id', $transaction->category_id);
            })
            ->with(['account:id,name', 'category:id,name'])
            ->orderBy('transaction_date', 'desc')
            ->limit(5)
            ->get();

        return view('transactions.show', compact(
            'transaction',
            'transferTransaction',
            'similarTransactions'
        ));
    }

    /**
     * Show the form for editing the specified transaction.
     * 
     * @param Transaction $transaction
     * @return \Illuminate\Contracts\View\View
     */
    public function edit(Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('update', $transaction);

        $user = Auth::user();

        // Get user's active accounts
        $accounts = $user->activeAccounts()->orderBy('sort_order')->get(['id', 'name', 'color', 'type', 'currency']);
        
        // Get categories
        $categories = Category::forUser($user->id)->active()->orderBy('type')->orderBy('name')->get(['id', 'name', 'color', 'type']);
        
        // Group categories by type
        $categoriesByType = $categories->groupBy('type');

        // Load split transactions if applicable
        if ($transaction->is_split) {
            $transaction->load('splitTransactions.category');
        }

        return view('transactions.edit', compact(
            'transaction',
            'accounts',
            'categories',
            'categoriesByType'
        ));
    }

    /**
     * Update the specified transaction in storage.
     * 
     * @param Request $request
     * @param Transaction $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('update', $transaction);

        // Store original values for balance adjustment
        $originalAmount = $transaction->amount;
        $originalType = $transaction->type;
        $originalAccountId = $transaction->account_id;
        $originalPending = $transaction->is_pending;

        // Validate the request (same validation as store method)
        $validated = $request->validate([
            'account_id' => [
                'required',
                'integer',
                Rule::exists('accounts', 'id')->where(function ($query) {
                    return $query->where('user_id', Auth::id())
                                 ->where('is_active', true);
                }),
            ],
            'category_id' => [
                'required',
                'integer',
                Rule::exists('categories', 'id')->where(function ($query) {
                    return $query->where(function ($q) {
                        $q->where('user_id', Auth::id())
                          ->orWhere('is_system', true);
                    })->where('is_active', true);
                }),
            ],
            'type' => [
                'required',
                'string',
                Rule::in(['income', 'expense', 'transfer']),
            ],
            'amount' => [
                'required',
                'numeric',
                'min:0.01',
                'max:999999.99',
                'decimal:0,2',
            ],
            'description' => [
                'required',
                'string',
                'max:255',
                'min:3',
            ],
            'transaction_date' => [
                'required',
                'date',
                'before_or_equal:today',
                'after:' . now()->subYears(10)->toDateString(),
            ],
            'notes' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'reference_number' => [
                'nullable',
                'string',
                'max:100',
            ],
            'is_pending' => [
                'boolean',
            ],
        ]);

        DB::beginTransaction();

        try {
            // Reverse the original balance impact if not pending
            if (!$originalPending) {
                $originalAccount = Account::find($originalAccountId);
                if ($originalAccount) {
                    if ($originalType === 'income') {
                        $originalAccount->decrement('balance', $originalAmount);
                    } elseif ($originalType === 'expense') {
                        $originalAccount->increment('balance', $originalAmount);
                    }
                }
            }

            // Update the transaction
            $transaction->update([
                'account_id' => $validated['account_id'],
                'category_id' => $validated['category_id'],
                'type' => $validated['type'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
                'transaction_date' => $validated['transaction_date'],
                'notes' => $validated['notes'],
                'reference_number' => $validated['reference_number'],
                'is_pending' => $validated['is_pending'] ?? false,
            ]);

            // Apply the new balance impact if not pending
            if (!$transaction->is_pending) {
                $newAccount = Account::find($validated['account_id']);
                if ($validated['type'] === 'income') {
                    $newAccount->increment('balance', $validated['amount']);
                } elseif ($validated['type'] === 'expense') {
                    $newAccount->decrement('balance', $validated['amount']);
                }
            }

            // Log the transaction update
            \Log::info('Transaction updated', [
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'changes' => $transaction->getChanges(),
            ]);

            DB::commit();

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaction updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Transaction update failed', [
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()
                ->withInput()
                ->withErrors(['general' => 'Failed to update transaction. Please try again.']);
        }
    }

    /**
     * Remove the specified transaction from storage.
     * 
     * @param Transaction $transaction
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Transaction $transaction)
    {
        // Ensure the user owns this transaction
        $this->authorize('delete', $transaction);

        DB::beginTransaction();

        try {
            // Reverse the balance impact if not pending
            if (!$transaction->is_pending) {
                $account = $transaction->account;
                if ($transaction->type === 'income') {
                    $account->decrement('balance', $transaction->amount);
                } elseif ($transaction->type === 'expense') {
                    $account->increment('balance', $transaction->amount);
                }
            }

            // Delete split transactions if any
            if ($transaction->is_split) {
                $transaction->splitTransactions()->delete();
            }

            // Handle transfer transaction deletion
            if ($transaction->transfer_transaction_id) {
                $transferTransaction = Transaction::find($transaction->transfer_transaction_id);
                if ($transferTransaction) {
                    // Reverse transfer balance impact
                    if (!$transferTransaction->is_pending) {
                        $transferAccount = $transferTransaction->account;
                        $transferAccount->decrement('balance', $transferTransaction->amount);
                    }
                    $transferTransaction->delete();
                }
            }

            // Log the transaction deletion
            \Log::info('Transaction deleted', [
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'account_id' => $transaction->account_id,
                'type' => $transaction->type,
                'amount' => $transaction->amount,
                'description' => $transaction->description,
            ]);

            // Delete the transaction
            $transaction->delete();

            DB::commit();

            return redirect()
                ->route('transactions.index')
                ->with('success', 'Transaction deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            \Log::error('Transaction deletion failed', [
                'user_id' => Auth::id(),
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'Failed to delete transaction. Please try again.'
            ]);
        }
    }

    /**
     * Check for potential duplicate transactions.
     * 
     * @param array $transactionData
     * @param Account $account
     * @return Transaction|null
     */
    private function checkForDuplicate(array $transactionData, Account $account)
    {
        return $account->transactions()
            ->where('amount', $transactionData['amount'])
            ->where('description', $transactionData['description'])
            ->where('transaction_date', $transactionData['transaction_date'])
            ->where('created_at', '>=', now()->subHours(24)) // Only check recent transactions
            ->first();
    }

    /**
     * Mark transaction as reconciled.
     * 
     * @param Transaction $transaction
     * @return \Illuminate\Http\JsonResponse
     */
    public function reconcile(Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $transaction->update(['is_reconciled' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Transaction marked as reconciled.',
        ]);
    }

    /**
     * Get transactions for API calls.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiIndex(Request $request)
    {
        $user = Auth::user();
        $limit = min($request->get('limit', 10), 50);

        $transactions = $user->transactions()
            ->with(['account:id,name', 'category:id,name,color'])
            ->orderBy('transaction_date', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'description' => $transaction->description,
                    'amount' => $transaction->signed_amount,
                    'formatted_amount' => $transaction->formatted_amount,
                    'type' => $transaction->type,
                    'date' => $transaction->transaction_date->toDateString(),
                    'account' => $transaction->account->name,
                    'category' => $transaction->category->name,
                    'is_pending' => $transaction->is_pending,
                ];
            });

        return response()->json(['transactions' => $transactions]);
    }
}