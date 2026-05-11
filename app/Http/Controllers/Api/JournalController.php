<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JournalController extends Controller
{
    public function index($patientId)
    {
        $notes = Journal::where('patient_id', $patientId)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        // CORRECTION 1 : 'humeur' devient 'nullable'
        $request->validate([
            'titre' => 'required|string',
            'note' => 'required|string',
            'humeur' => 'nullable|string', 
        ]);

        try {
            $journal = \App\Models\Journal::create([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'patient_id' => Auth::id(), 
                'titre'      => $request->titre,
                'note'       => $request->note,
                // CORRECTION 2 : Si pas d'humeur envoyée, on met une valeur vide ou par défaut
                'humeur'     => $request->humeur ?? '---', 
                'created_at' => now(),
            ]);

            return response()->json($journal, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $journal = Journal::findOrFail($id);
        
        if ($journal->patient_id !== Auth::id() && !Auth::user()->is_admin) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        // CORRECTION 3 : update gère aussi l'humeur comme facultative
        $journal->update($request->only(['titre', 'note', 'humeur']));

        return response()->json($journal);
    }

    public function destroy($id)
    {
        $journal = Journal::findOrFail($id);
        
        if ($journal->patient_id !== Auth::id()) {
            return response()->json(['message' => 'Non autorisé'], 403);
        }

        $journal->delete();
        return response()->json(['message' => 'Note supprimée']);
    }
}