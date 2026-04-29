<?php

namespace App\Http\Controllers\Affiliate;

use App\Http\Controllers\Controller;
use App\Models\Affiliate;
use App\Services\AffiliateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('affiliate.register');
    }

    public function register(Request $request, AffiliateService $affiliateService)
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:affiliates,email'],
            'phone' => ['required', 'string', 'max:60'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'payment_method' => ['required', 'string', 'max:120'],
            'payment_details' => ['required', 'string', 'max:2000'],
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
        return view('affiliate.login');
    }

    public function login(Request $request)
    {
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
}
