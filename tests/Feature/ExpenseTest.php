<?php

namespace Tests\Feature;

use App\Models\Expense;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    public function test_expenses_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('expenses.index'));
        $response->assertStatus(200);
    }

    public function test_expenses_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('expenses.create')
            ->set('description', 'Test Expense')
            ->set('amount', 100.50)
            ->set('category', 'Materials')
            ->set('date', now()->format('Y-m-d'))
            ->set('payment_method', 'Cash')
            ->set('reference_number', 'REF123')
            ->set('notes', 'Test expense notes')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'user_id' => $user->id,
            'description' => 'Test Expense',
            'amount' => 100.50,
            'category' => 'Materials',
            'payment_method' => 'Cash',
            'reference_number' => 'REF123',
            'notes' => 'Test expense notes',
        ]);
    }

    public function test_expense_can_be_updated(): void
    {
        $user = User::factory()->create();
        $expense = Expense::factory()->create([
            'user_id' => $user->id,
            'description' => 'Test Expense',
            'amount' => 100.50,
            'category' => 'Materials',
        ]);

        $this->actingAs($user);

        $response = Volt::test('expenses.edit', ['expense' => $expense])
            ->set('description', 'Updated Expense')
            ->set('amount', 200.75)
            ->set('category', 'Utilities')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('expenses', [
            'id' => $expense->id,
            'description' => 'Updated Expense',
            'amount' => 200.75,
            'category' => 'Utilities',
        ]);
    }

    public function test_expense_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('expenses.create')
            ->set('description', '')
            ->set('amount', -10)
            ->set('category', '')
            ->set('date', '')
            ->call('save');

        $response->assertHasErrors(['description', 'amount', 'category', 'date']);
    }

    public function test_expense_categories_are_valid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $validCategories = [
            'Materials',
            'Utilities',
            'Rent',
            'Salaries',
            'Equipment',
            'Marketing',
            'Transportation',
            'Maintenance',
            'Other'
        ];

        foreach ($validCategories as $category) {
            $response = Volt::test('expenses.create')
                ->set('description', 'Test Expense')
                ->set('amount', 100)
                ->set('category', $category)
                ->set('date', now()->format('Y-m-d'))
                ->call('save');

            $response->assertHasNoErrors('category');
        }

        // Test invalid category
        $response = Volt::test('expenses.create')
            ->set('description', 'Test Expense')
            ->set('amount', 100)
            ->set('category', 'InvalidCategory')
            ->set('date', now()->format('Y-m-d'))
            ->call('save');

        $response->assertHasErrors('category');
    }

    public function test_user_cannot_edit_others_expenses(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $expense = Expense::factory()->create([
            'user_id' => $user1->id,
            'description' => 'Test Expense',
        ]);

        $this->actingAs($user2);

        $response = $this->get(route('expenses.edit', $expense));
        $response->assertStatus(403);
    }
}
