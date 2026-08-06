<?php

namespace App\Http\Controllers;

use App\Models\Steps;
use Illuminate\Support\Facades\Auth;

class StepController extends Controller
{
    /**
     * Toggle the completed state of the specified resource.
     */
    public function update(Steps $step)
    {

        $step->update([
            'completed' => ! $step->completed,
        ]);

        return back();
    }
}
