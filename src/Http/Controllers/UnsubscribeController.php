<?php

namespace Commisoft\FilamentComms\Http\Controllers;

use Commisoft\FilamentComms\Services\PreferenceService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class UnsubscribeController extends Controller
{
    public function show(Request $request, PreferenceService $preferences)
    {
        $email = (string) $request->query('email');
        $category = $request->query('category');
        $state = $preferences->getPreferencesForEmail($email);

        return view('filament-comms::preferences', [
            'email' => $preferences->normalizeEmail($email),
            'highlightedCategory' => $category,
            'categories' => config('filament-comms.categories', []),
            'preferences' => $state['preferences'],
            'unsubscribedAll' => $state['unsubscribed_all'],
            'unsubscribeReason' => $state['unsubscribe_reason'],
            'saved' => session('saved', false),
        ]);
    }

    public function update(Request $request, PreferenceService $preferences)
    {
        $email = (string) $request->query('email');
        $unsubscribedAll = $request->boolean('unsubscribed_all');
        $reason = $request->input('unsubscribe_reason');

        $submittedPrefs = $request->input('preferences', []);
        $categories = array_keys(config('filament-comms.categories', []));
        $cleanPrefs = [];
        foreach ($categories as $cat) {
            $cleanPrefs[$cat] = isset($submittedPrefs[$cat]);
        }

        $preferences->updatePreferences(
            email: $email,
            categoryMap: $cleanPrefs,
            unsubscribedAll: $unsubscribedAll,
            reason: $reason,
        );

        return redirect()->to($request->fullUrl())->with('saved', true);
    }

    public function oneClick(Request $request, PreferenceService $preferences)
    {
        $email = (string) $request->query('email');
        $category = (string) $request->query('category', 'newsletters');

        $preferences->unsubscribeCategory($email, $category, 'One-click email header unsubscribe');

        return response('Unsubscribed', 200);
    }
}
