<?php

namespace App\Http\Middleware;

use App\Models\Central\Company;
use Illuminate\Http\Request;
use Inertia\Middleware;
use Tighten\Ziggy\Ziggy;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): string|null
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $company = Company::first();
        if ($user) {
            try {
                $user->rol_name = $user->rol_name ?? '';
                $user->is_tenant = !empty(tenant('id'));
                $user->avatar_url = $user->getImageUrl() ?? '';
                $user->company_logo = $company ? $company->logo_url : '';
            } catch (\Exception $e) {
                
            }
            
        }
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user()
            ],
            'ziggy' => fn () => [
                ...(new Ziggy)->toArray(),
                'location' => $request->url(),
            ],
            'flash' => [
                'message' => fn () => $request->session()->get('message'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'files' => fn () => $request->session()->get('files'),
            ]
        ];
    }
}
