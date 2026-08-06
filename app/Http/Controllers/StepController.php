<?php

namespace App\Http\Controllers;

use App\Models\Steps;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class StepController extends Controller
{
    /**
     * Toggle the completed state of the specified resource.
     */
    public function update(Steps $step)
    {
        Gate::authorize('workWith', $step->idea);

        $step->update([
            'completed' => ! $step->completed,
        ]);

        return back();
    }
}
