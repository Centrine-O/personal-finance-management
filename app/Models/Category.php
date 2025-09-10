<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;

/**
 * Category Model
 * 
 * Categories help organize income and expenses for budgeting and reporting.
 * Supports hierarchical structure (parent/child categories).
 * 
 * Examples:
 * - Food (parent)
 *   - Groceries (child)
 *   - Restaurants (child)
 * - Transportation (parent)
 *   - Gas (child)
 *   - Public Transit (child)
 * 
 * Key Features:
 * - Hierarchical categories (parent/child relationships)
 * - System categories (available to all users) vs user-specific
 * - Visual customization (colors, icons)
 * - Budget integration
 * - Transaction categorization
 */
class Category extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'user_id',
        'parent_id',
        'name',
        'description',
        'type',
        'color',
        'icon',
        'is_active',
        'is_budgetable',
        'suggested_budget',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'is_active' => 'boolean',
        'is_budgetable' => 'boolean',
        'suggested_budget' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    /**
     * Boot the model.
     * 
     * This method runs when the model is initialized.
     * We use it to set up event listeners and default values.
     */
    protected static function boot()
    {
        parent::boot();

        // When creating a new category, automatically set sort_order
        static::creating(function ($category) {
            if (!$category->sort_order) {
                // Find the highest sort_order for this user/parent and add 1
                $maxOrder = static::where('user_id', $category->user_id)
                                  ->where('parent_id', $category->parent_id)
                                  ->max('sort_order');
                                  
                $category->sort_order = ($maxOrder ?? 0) + 1;
            }
        });
    }

    // ========================================
    // RELATIONSHIPS
    // ========================================

    /**
     * Get the user who owns this category.
     * 
     * If user_id is null, this is a system category available to all users.
     * 
     * Usage: $category->user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent category (if this is a subcategory).
     * 
     * Usage: $category->parent
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get all child categories (subcategories).
     * 
     * Usage: $category->children
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')
                    ->where('is_active', true)
                    ->orderBy('sort_order');
    }

    /**
     * Get all transactions in this category.
     * 
     * Usage: $category->transactions
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all budget allocations for this category.
     * 
     * Usage: $category->budgetCategories
     */
    public function budgetCategories(): HasMany
    {
        return $this->hasMany(BudgetCategory::class);
    }

    /**
     * Get all bills in this category.
     * 
     * Usage: $category->bills
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all recurring transactions in this category.
     * 
     * Usage: $category->recurringTransactions
     */
    public function recurringTransactions(): HasMany
    {
        return $this->hasMany(RecurringTransaction::class);
    }

    // ========================================
    // QUERY SCOPES
    // ========================================
    
    /**
     * Scope to get only active categories.
     * 
     * Usage: Category::active()->get()
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to get only budgetable categories.
     * 
     * Usage: Category::budgetable()->get()
     */
    public function scopeBudgetable($query)
    {
        return $query->where('is_budgetable', true);
    }

    /**
     * Scope to get only top-level categories (no parent).
     * 
     * Usage: Category::topLevel()->get()
     */
    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Scope to get categories by type.
     * 
     * Usage: Category::ofType('expense')->get()
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope to get categories for a specific user (including system categories).
     * 
     * Usage: Category::forUser($userId)->get()
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('user_id', $userId)    // User's personal categories
              ->orWhereNull('user_id');      // System categories (available to all)
        });
    }

    // ========================================
    // ACCESSORS & MUTATORS
    // ========================================

    /**
     * Get the category's full hierarchical name.
     * 
     * Example: "Food > Restaurants" for a restaurant subcategory
     * 
     * Usage: $category->full_name
     */
    public function getFullNameAttribute(): string
    {
        $names = collect([$this->name]);
        
        $parent = $this->parent;
        while ($parent) {
            $names->prepend($parent->name);
            $parent = $parent->parent;
        }
        
        return $names->join(' > ');
    }

    /**
     * Get the category depth level (how many levels deep).
     * 
     * Top-level categories have depth 0, their children have depth 1, etc.
     * 
     * Usage: $category->depth
     */
    public function getDepthAttribute(): int
    {
        $depth = 0;
        $parent = $this->parent;
        
        while ($parent) {
            $depth++;
            $parent = $parent->parent;
        }
        
        return $depth;
    }

    /**
     * Check if this category is a system category (available to all users).
     * 
     * Usage: $category->is_system
     */
    public function getIsSystemAttribute(): bool
    {
        return $this->user_id === null;
    }

    /**
     * Check if this category has child categories.
     * 
     * Usage: $category->has_children
     */
    public function getHasChildrenAttribute(): bool
    {
        return $this->children()->exists();
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    /**
     * Get all descendants (children, grandchildren, etc.) of this category.
     * 
     * Returns a flat collection of all subcategories at any depth.
     */
    public function getAllDescendants(): Collection
    {
        $descendants = collect();
        
        foreach ($this->children as $child) {
            $descendants->push($child);
            $descendants = $descendants->merge($child->getAllDescendants());
        }
        
        return $descendants;
    }

    /**
     * Get all ancestors (parent, grandparent, etc.) of this category.
     * 
     * Returns a collection from immediate parent to root category.
     */
    public function getAllAncestors(): Collection
    {
        $ancestors = collect();
        
        $parent = $this->parent;
        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }
        
        return $ancestors;
    }

    /**
     * Check if this category is a child (or descendant) of another category.
     */
    public function isChildOf(Category $category): bool
    {
        return $this->getAllAncestors()->contains('id', $category->id);
    }

    /**
     * Check if this category is a parent (or ancestor) of another category.
     */
    public function isParentOf(Category $category): bool
    {
        return $category->isChildOf($this);
    }

    /**
     * Get the root category (top-most parent).
     * 
     * If this is already a root category, returns itself.
     */
    public function getRoot(): Category
    {
        $current = $this;
        
        while ($current->parent) {
            $current = $current->parent;
        }
        
        return $current;
    }

    /**
     * Calculate total spending in this category for a date range.
     * 
     * Includes spending in all subcategories.
     */
    public function getTotalSpending($startDate = null, $endDate = null): float
    {
        // Get all category IDs (this category + all descendants)
        $categoryIds = collect([$this->id])
            ->merge($this->getAllDescendants()->pluck('id'));

        $query = Transaction::whereIn('category_id', $categoryIds)
                           ->where('type', 'expense');

        if ($startDate) {
            $query->where('transaction_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->where('transaction_date', '<=', $endDate);
        }

        return $query->sum('amount');
    }

    /**
     * Get the category hierarchy as a tree structure.
     * 
     * This is useful for displaying categories in dropdowns or tree views.
     */
    public static function getHierarchy($userId = null): Collection
    {
        $query = static::active()
                      ->topLevel()
                      ->orderBy('sort_order')
                      ->orderBy('name');

        if ($userId) {
            $query->forUser($userId);
        }

        return $query->get()->each(function ($category) {
            $category->load(['children' => function ($query) {
                $query->orderBy('sort_order')->orderBy('name');
            }]);
        });
    }

    /**
     * Reorder categories within the same parent/user group.
     */
    public function reorder(array $categoryIds): void
    {
        foreach ($categoryIds as $order => $categoryId) {
            static::where('id', $categoryId)
                  ->where('user_id', $this->user_id)
                  ->where('parent_id', $this->parent_id)
                  ->update(['sort_order' => $order + 1]);
        }
    }
}