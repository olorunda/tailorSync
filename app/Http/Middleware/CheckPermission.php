<?php

namespace App\Http\Middleware;

use App\Models\Appointment;
use App\Models\Client;
use App\Models\Design;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Measurement;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Task;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\matches;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  $permission  The permission required to access the route
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Ensure the roleRelation is loaded
        $user = Auth::user();
        if (!$user->hasPermission($permission)) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthorized. You do not have the required permission.'], 403);
            }

            return redirect()->route('dashboard')->with('error', 'You do not have permission to access this feature , please contact your administrator.');
        }


        if (preg_match('/^[^\/]+\/\d+$/', request()->path())) {
            $path = explode('/', request()->path());
            return $this->reddirectIfNotOwner($path,$next,$request);
        }

        if (preg_match('/^[^\/]+\/\d+\/edit$/', request()->path())) {     //edid
            $path = explode('/', request()->path());
            return $this->reddirectIfNotOwner($path,$next,$request);
        }
        return $next($request);
    }

    public function reddirectIfNotOwner($path, $next, $request)
    {
        $modelMap = [
            'tasks' => ['model' => Task::class, 'special' => 'assigned_to'],
            'expenses' => ['model' => Expense::class],
            'appointments' => ['model' => Appointment::class],
            'measurements' => ['model' => Measurement::class],
            'designs' => ['model' => Design::class],
            'invoices' => ['model' => Invoice::class],
            'orders' => ['model' => Order::class],
            'payments' => ['model' => Payment::class],
            'clients' => ['model' => Client::class],
            'teams' => ['model' => User::class, 'special' => 'id']
        ];

        if (!isset($modelMap[$path[0]])) {
            return $next($request);
        }

        $model = $modelMap[$path[0]];
        $record = $model['model']::findOrFail($path[1]);

        $firstId = isset($model['special']) ? $record->{$model['special']} : $record->user_id;
        $secondId = $record instanceof User ? $record->id : $record->user->parent_id;

        if (!array_intersect([$firstId, $secondId], [Auth::id(), Auth::user()->parent_id])) {
            return redirect(route($path[0] . '.index'));
        }

        return $next($request);
    }
}
