<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class AuthController extends Controller
{
    public function showRegister()
    {
        if (!$this->affiliateTablesReady()) {
            return view('affiliate.register')->withErrors([
                'setup' => 'Affiliate registration is being set up. Please run the database migrations, then try again.',
            ]);
        }

        return view('affiliate.register');
    }

    public function register(Request $request, AffiliateService $affiliateService)
    {
        if (!$this->affiliateTablesReady()) {
            return back()->withErrors([
                'setup' => 'Affiliate registration is not ready yet. Run php artisan migrate to create the affiliate tables.',
            ])->withInput();
        }

        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:affiliates,email'],
            'phone' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'payment_method' => ['required', 'string', 'max:120'],
            'payment_details' => ['required', 'string', 'max:2000'],
            'policy_acceptance' => ['accepted'],
        ]);

        $affiliate = Affiliate::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'status' => Affiliate::STATUS_PENDING,
        ]);

        $affiliateService->notify($affiliate, 'Affiliate registration received', 'Thanks for applying. Your affiliate account is pending admin approval.');

        return redirect()->route('affiliate.login')
            ->with('success', 'Registration received. You can log in after admin approval.');
    }

    public function showLogin()
    {
        if (!$this->affiliateTablesReady()) {
            return view('affiliate.login')->withErrors([
                'setup' => 'Affiliate login is being set up. Please run the database migrations, then try again.',
            ]);
        }

        return view('affiliate.login');
    }

    public function login(Request $request)
    {
        if (!$this->affiliateTablesReady()) {
            return back()->withErrors([
                'setup' => 'Affiliate login is not ready yet. Run php artisan migrate to create the affiliate tables.',
            ])->withInput();
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $affiliate = Affiliate::where('email', $credentials['email'])->first();
        if (!$affiliate || !Hash::check($credentials['password'], (string) $affiliate->password)) {
            return back()->withErrors(['email' => 'Invalid affiliate credentials.'])->withInput();
        }

        if (!$affiliate->isApproved()) {
            return back()->withErrors(['email' => 'Your affiliate account is not approved yet.'])->withInput();
        }

        $request->session()->put('affiliate_id', $affiliate->id);
        $request->session()->regenerate();
        $affiliate->forceFill(['last_login_at' => now()])->save();

        return redirect()->route('affiliate.dashboard');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('affiliate_id');
        $request->session()->regenerateToken();

        return redirect()->route('affiliate.login');
    }

    private function affiliateTablesReady(): bool
    {
        return Schema::hasTable('affiliates') && Schema::hasTable('affiliate_settings');
    }
}
