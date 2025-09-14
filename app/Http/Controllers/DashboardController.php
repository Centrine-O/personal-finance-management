<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;

/**
 * Dashboard Controller
 * 
 * This controller handles the main dashboard view for authenticated users.
 * The dashboard provides a comprehensive overview of the user's financial status,
 * including account balances, recent transactions, budget progress, and financial goals.
 * 
 * Key Features:
 * - Financial overview and net worth calculation
 * - Recent transaction history
 * - Budget progress and alerts
 * - Account balance summaries
 * - Financial goal tracking
 * - Quick action buttons
 * - Responsive design for mobile and desktop
 * 
 * Why we need a dashboard:
 * - Provides users with a quick financial overview
 * - Shows the most important information first
 * - Helps users make informed financial decisions
 * - Encourages regular engagement with the app
 * - Serves as a navigation hub to other features
 */
class DashboardController extends Controller
{
    /**
     * Create a new controller instance.
     * 
     * Apply middleware to ensure only authenticated users can access the dashboard.
     * This is critical for financial applications where data security is paramount.
     */
    public function __construct()
    {
        // Require authentication for all dashboard methods
        $this->middleware('auth');
        
        // Require email verification for financial data access
        $this->middleware('verified');
        
        // Check account status (active, not suspended, not locked)
        $this->middleware('check.account.status');
    }

    /**
     * Display the main dashboard.
     * 
     * This method gathers all the necessary data to display a comprehensive
     * financial overview to the user. It's optimized for performance by
     * using eager loading and limiting data to what's actually needed.
     * 
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    public function index(Request $request)
    {
        // Get the authenticated user
        // We know the user is authenticated due to middleware
        $user = Auth::user();

        // Define the time period for recent activity (last 30 days)
        $recentActivityPeriod = now()->subDays(30);

        // ========================================
        // FINANCIAL OVERVIEW DATA
        // ========================================

        // Calculate net worth (assets - liabilities)
        // This gives users a quick snapshot of their overall financial health
        $netWorth = $user->calculateNetWorth();

        // Get monthly income and expenses for the current month
        // This helps users understand their cash flow
        $currentMonth = now();
        $monthlyIncome = $user->getMonthlyIncome($currentMonth);
        $monthlyExpenses = $user->getMonthlyExpenses($currentMonth);
        
        // Calculate savings rate (how much they're saving each month)
        $monthlySavings = $monthlyIncome - $monthlyExpenses;
        $savingsRate = $monthlyIncome > 0 ? ($monthlySavings / $monthlyIncome) * 100 : 0;

        // ========================================
        // ACCOUNT BALANCES DATA
        // ========================================

        // Get all active accounts with their current balances
        // We'll group them by type for better visualization
        $accounts = $user->activeAccounts()
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        // Group accounts by type for better organization on the dashboard
        $accountsByType = $accounts->groupBy('type');

        // Calculate total balances by account type
        $accountSummary = [
            'checking' => $accounts->where('type', 'checking')->sum('balance'),
            'savings' => $accounts->where('type', 'savings')->sum('balance'),
            'investment' => $accounts->where('type', 'investment')->sum('balance'),
            'credit' => $accounts->where('type', 'credit')->sum('balance'), // This will be negative
            'loan' => $accounts->where('type', 'loan')->sum('balance'), // This will be negative
        ];

        // Identify accounts with low balances for alerts
        $lowBalanceAccounts = $user->getLowBalanceAccounts();

        // ========================================
        // RECENT TRANSACTIONS DATA
        // ========================================

        // Get recent transactions for the activity feed
        // We'll limit this to the 10 most recent to keep the dashboard fast
        $recentTransactions = $user->transactions()
            ->with(['account:id,name', 'category:id,name,color'])
            ->where('transaction_date', '>=', $recentActivityPeriod)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // ========================================
        // BUDGET PROGRESS DATA
        // ========================================

        // Get the current active budget
        $currentBudget = $user->activeBudget();
        
        $budgetProgress = null;
        $budgetAlerts = [];
        
        if ($currentBudget) {
            // Calculate budget progress and identify overspending
            $budgetProgress = [
                'budget' => $currentBudget,
                'income_progress' => $currentBudget->planned_income > 0 
                    ? ($currentBudget->actual_income / $currentBudget->planned_income) * 100 
                    : 0,
                'expense_progress' => $currentBudget->planned_expenses > 0 
                    ? ($currentBudget->actual_expenses / $currentBudget->planned_expenses) * 100 
                    : 0,
                'remaining_budget' => $currentBudget->remaining_budget,
                'is_overspent' => $currentBudget->is_overspent,
            ];

            // Check for budget category overspending
            $overspentCategories = $currentBudget->budgetCategories()
                ->with('category:id,name,color')
                ->whereRaw('spent_amount > allocated_amount')
                ->get();

            // Create budget alerts for overspent categories
            foreach ($overspentCategories as $budgetCategory) {
                $overage = $budgetCategory->spent_amount - $budgetCategory->allocated_amount;
                $budgetAlerts[] = [
                    'type' => 'overspent',
                    'category' => $budgetCategory->category->name,
                    'amount' => $overage,
                    'message' => "You've overspent by " . number_format($overage, 2) . " in " . $budgetCategory->category->name,
                ];
            }
        }

        // ========================================
        // FINANCIAL GOALS DATA
        // ========================================

        // Get active financial goals with progress
        $activeGoals = $user->goals()
            ->where('status', 'active')
            ->where('target_date', '>=', now())
            ->orderBy('target_date')
            ->limit(5)
            ->get();

        // Calculate goal progress and identify goals that need attention
        $goalAlerts = [];
        foreach ($activeGoals as $goal) {
            // Check if goal is behind schedule
            $timeElapsed = now()->diffInDays($goal->created_at);
            $totalTime = $goal->created_at->diffInDays($goal->target_date);
            $timeProgress = $totalTime > 0 ? ($timeElapsed / $totalTime) * 100 : 0;
            $amountProgress = $goal->target_amount > 0 ? ($goal->current_amount / $goal->target_amount) * 100 : 0;

            if ($timeProgress > $amountProgress + 20) { // Behind by more than 20%
                $goalAlerts[] = [
                    'type' => 'behind_schedule',
                    'goal' => $goal,
                    'message' => "Your '{$goal->name}' goal is behind schedule. Consider increasing your contributions.",
                ];
            }
        }

        // ========================================
        // UPCOMING BILLS DATA
        // ========================================

        // Get upcoming bills in the next 7 days
        $upcomingBills = $user->bills()
            ->where('is_active', true)
            ->where('next_due_date', '<=', now()->addDays(7))
            ->where('next_due_date', '>=', now())
            ->orderBy('next_due_date')
            ->limit(5)
            ->get();

        // ========================================
        // INSIGHTS AND RECOMMENDATIONS
        // ========================================

        // Generate personalized insights based on spending patterns
        $insights = $this->generateFinancialInsights($user, $monthlyIncome, $monthlyExpenses, $recentTransactions);

        // ========================================
        // QUICK STATS FOR CARDS
        // ========================================

        $quickStats = [
            'total_accounts' => $accounts->count(),
            'total_transactions_this_month' => $user->transactions()
                ->whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->count(),
            'largest_expense_this_month' => $user->transactions()
                ->where('type', 'expense')
                ->whereYear('transaction_date', $currentMonth->year)
                ->whereMonth('transaction_date', $currentMonth->month)
                ->max('amount'),
            'average_daily_spending' => $monthlyExpenses > 0 ? $monthlyExpenses / $currentMonth->day : 0,
        ];

        // ========================================
        // RETURN VIEW WITH ALL DATA
        // ========================================

        return view('dashboard', compact(
            // User and basic info
            'user',
            
            // Financial overview
            'netWorth',
            'monthlyIncome',
            'monthlyExpenses',
            'monthlySavings',
            'savingsRate',
            
            // Account data
            'accounts',
            'accountsByType',
            'accountSummary',
            'lowBalanceAccounts',
            
            // Transaction data
            'recentTransactions',
            
            // Budget data
            'budgetProgress',
            'budgetAlerts',
            
            // Goals data
            'activeGoals',
            'goalAlerts',
            
            // Bills data
            'upcomingBills',
            
            // Insights and stats
            'insights',
            'quickStats'
        ));
    }

    /**
     * Get dashboard data as JSON for API calls.
     * 
     * This method returns the same data as the index method but formatted
     * for API consumption. Useful for AJAX updates and mobile apps.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function apiData(Request $request)
    {
        $user = Auth::user();

        // Get basic financial overview
        $data = [
            'net_worth' => $user->calculateNetWorth(),
            'monthly_income' => $user->getMonthlyIncome(now()),
            'monthly_expenses' => $user->getMonthlyExpenses(now()),
            'accounts_count' => $user->activeAccounts()->count(),
            'recent_transactions' => $user->transactions()
                ->with(['account:id,name', 'category:id,name'])
                ->orderBy('transaction_date', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'description' => $transaction->description,
                        'amount' => $transaction->signed_amount,
                        'formatted_amount' => $transaction->formatted_amount,
                        'date' => $transaction->transaction_date->toDateString(),
                        'account' => $transaction->account->name,
                        'category' => $transaction->category->name,
                    ];
                }),
            'timestamp' => now()->toISOString(),
        ];

        return response()->json($data);
    }

    /**
     * Refresh specific dashboard components.
     * 
     * This method allows for partial dashboard updates without
     * reloading the entire page. Useful for real-time updates.
     * 
     * @param Request $request
     * @param string $component
     * @return \Illuminate\Http\JsonResponse
     */
    public function refreshComponent(Request $request, $component)
    {
        $user = Auth::user();

        switch ($component) {
            case 'accounts':
                $accounts = $user->activeAccounts()->get();
                return response()->json(['accounts' => $accounts]);

            case 'transactions':
                $recentTransactions = $user->transactions()
                    ->with(['account:id,name', 'category:id,name'])
                    ->orderBy('transaction_date', 'desc')
                    ->limit(10)
                    ->get();
                return response()->json(['transactions' => $recentTransactions]);

            case 'budget':
                $budget = $user->activeBudget();
                return response()->json(['budget' => $budget]);

            case 'goals':
                $goals = $user->goals()
                    ->where('status', 'active')
                    ->orderBy('target_date')
                    ->limit(5)
                    ->get();
                return response()->json(['goals' => $goals]);

            default:
                return response()->json(['error' => 'Invalid component'], 400);
        }
    }

    /**
     * Generate personalized financial insights.
     * 
     * This method analyzes the user's spending patterns and financial data
     * to provide actionable insights and recommendations.
     * 
     * @param \App\Models\User $user
     * @param float $monthlyIncome
     * @param float $monthlyExpenses
     * @param \Illuminate\Database\Eloquent\Collection $recentTransactions
     * @return array
     */
    private function generateFinancialInsights($user, $monthlyIncome, $monthlyExpenses, $recentTransactions)
    {
        $insights = [];

        // Savings rate insight
        if ($monthlyIncome > 0) {
            $savingsRate = (($monthlyIncome - $monthlyExpenses) / $monthlyIncome) * 100;
            
            if ($savingsRate < 10) {
                $insights[] = [
                    'type' => 'warning',
                    'icon' => 'exclamation-triangle',
                    'title' => 'Low Savings Rate',
                    'message' => "You're saving {$savingsRate:.1f}% of your income. Consider aiming for at least 20%.",
                    'action' => 'Review your expenses and create a budget',
                ];
            } elseif ($savingsRate >= 20) {
                $insights[] = [
                    'type' => 'success',
                    'icon' => 'check-circle',
                    'title' => 'Great Savings Rate!',
                    'message' => "You're saving {$savingsRate:.1f}% of your income. Keep up the excellent work!",
                    'action' => 'Consider investing your excess savings',
                ];
            }
        }

        // Spending pattern insights
        $topCategories = $recentTransactions
            ->where('type', 'expense')
            ->groupBy('category.name')
            ->map(function ($transactions) {
                return $transactions->sum('amount');
            })
            ->sortDesc()
            ->take(3);

        if ($topCategories->isNotEmpty()) {
            $topCategory = $topCategories->keys()->first();
            $topCategoryAmount = $topCategories->first();
            $percentage = $monthlyExpenses > 0 ? ($topCategoryAmount / $monthlyExpenses) * 100 : 0;

            if ($percentage > 30) {
                $insights[] = [
                    'type' => 'info',
                    'icon' => 'chart-pie',
                    'title' => 'High Category Spending',
                    'message' => "{$percentage:.1f}% of your expenses are in '{$topCategory}'. Consider if this aligns with your priorities.",
                    'action' => 'Review transactions in this category',
                ];
            }
        }

        // Emergency fund insight
        $liquidSavings = $user->activeAccounts()
            ->whereIn('type', ['checking', 'savings'])
            ->sum('balance');
        
        $emergencyFundMonths = $monthlyExpenses > 0 ? $liquidSavings / $monthlyExpenses : 0;

        if ($emergencyFundMonths < 3) {
            $insights[] = [
                'type' => 'warning',
                'icon' => 'shield-exclamation',
                'title' => 'Emergency Fund Low',
                'message' => "You have {$emergencyFundMonths:.1f} months of expenses saved. Aim for 3-6 months.",
                'action' => 'Set up automatic transfers to savings',
            ];
        }

        // Credit utilization insight (if applicable)
        $creditAccounts = $user->activeAccounts()->where('type', 'credit')->get();
        if ($creditAccounts->isNotEmpty()) {
            $totalCreditUsed = abs($creditAccounts->sum('balance'));
            $totalCreditLimit = $creditAccounts->sum('credit_limit');
            
            if ($totalCreditLimit > 0) {
                $utilizationRate = ($totalCreditUsed / $totalCreditLimit) * 100;
                
                if ($utilizationRate > 30) {
                    $insights[] = [
                        'type' => 'warning',
                        'icon' => 'credit-card',
                        'title' => 'High Credit Utilization',
                        'message' => "Your credit utilization is {$utilizationRate:.1f}%. Try to keep it below 30%.",
                        'action' => 'Pay down credit card balances',
                    ];
                }
            }
        }

        return $insights;
    }

    /**
     * Export dashboard data to PDF.
     * 
     * This method generates a PDF report of the user's financial dashboard
     * that they can save or print for their records.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function exportPdf(Request $request)
    {
        // This would generate a PDF using a library like TCPDF or MPDF
        // For now, we'll return a placeholder response
        
        return response()->json([
            'message' => 'PDF export feature will be implemented in a future version.',
            'status' => 'coming_soon'
        ]);
    }
}