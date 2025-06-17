<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;
use Tests\TestCase;

class TeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_team_page_is_displayed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get(route('team.index'));
        $response->assertStatus(200);
    }

    public function test_team_members_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('team.create')
            ->set('name', 'Test Team Member')
            ->set('email', 'team@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('phone', '1234567890')
            ->set('position', 'Designer')
            ->set('role', 'team_member')
            ->set('notes', 'Test team member notes')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'name' => 'Test Team Member',
            'email' => 'team@example.com',
            'phone' => '1234567890',
            'position' => 'Designer',
            'role' => 'team_member',
            'notes' => 'Test team member notes',
        ]);
    }

    public function test_team_member_details_can_be_viewed(): void
    {
        $user = User::factory()->create();
        $teamMember = User::factory()->create([
            'name' => 'Test Team Member',
            'email' => 'team@example.com',
            'role' => 'designer',
        ]);

        $this->actingAs($user);

        $response = $this->get(route('team.show', $teamMember));
        $response->assertStatus(200);
        $response->assertSee('Test Team Member');
        $response->assertSee('team@example.com');
        $response->assertSee('Designer');
    }

    public function test_team_member_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $teamMember = User::factory()->create([
            'name' => 'Test Team Member',
            'email' => 'team@example.com',
            'role' => 'team_member',
        ]);

        $this->actingAs($user);

        $response = Volt::test('team.edit', ['teamMember' => $teamMember])
            ->set('name', 'Updated Team Member')
            ->set('email', 'updated@example.com')
            ->set('role', 'designer')
            ->call('save');

        $response->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $teamMember->id,
            'name' => 'Updated Team Member',
            'email' => 'updated@example.com',
            'role' => 'designer',
        ]);
    }

    public function test_team_member_validation_works(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = Volt::test('team.create')
            ->set('name', '')
            ->set('email', 'not-an-email')
            ->set('password', 'short')
            ->set('password_confirmation', 'different')
            ->set('role', 'invalid-role')
            ->call('save');

        $response->assertHasErrors(['name', 'email', 'password', 'role']);
    }

    public function test_team_member_roles_are_valid(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $validRoles = [
            'admin',
            'manager',
            'team_member',
            'tailor',
            'designer'
        ];

        foreach ($validRoles as $role) {
            $response = Volt::test('team.create')
                ->set('name', 'Test Team Member')
                ->set('email', "team_{$role}@example.com")
                ->set('password', 'password123')
                ->set('password_confirmation', 'password123')
                ->set('role', $role)
                ->call('save');

            $response->assertHasNoErrors('role');
        }

        // Test invalid role
        $response = Volt::test('team.create')
            ->set('name', 'Test Team Member')
            ->set('email', 'team_invalid@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'invalid-role')
            ->call('save');

        $response->assertHasErrors('role');
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $teamMember = User::factory()->create([
            'name' => 'Test Team Member',
            'email' => 'team@example.com',
        ]);

        $this->actingAs($user);

        $response = Volt::test('team.edit', ['teamMember' => $teamMember])
            ->set('name', 'Test Team Member')
            ->set('email', 'team@example.com')
            ->set('password', 'newpassword123')
            ->set('password_confirmation', 'newpassword123')
            ->call('save');

        $response->assertHasNoErrors();

        // Verify the password was hashed
        $updatedTeamMember = User::find($teamMember->id);
        $this->assertTrue(Hash::check('newpassword123', $updatedTeamMember->password));
    }

    public function test_non_admin_cannot_create_admin_users(): void
    {
        $user = User::factory()->create(['role' => 'team_member']);
        $this->actingAs($user);

        $response = Volt::test('team.create')
            ->set('name', 'Admin User')
            ->set('email', 'admin@example.com')
            ->set('password', 'password123')
            ->set('password_confirmation', 'password123')
            ->set('role', 'admin')
            ->call('save');

        // This should fail with a permission error
        $response->assertStatus(403);
    }
}
