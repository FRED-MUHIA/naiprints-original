<div class="cart-popup" data-cart-popup aria-hidden="true">
    <div class="cart-overlay" data-cart-close></div>
    <div class="cart-panel" role="dialog" aria-modal="true" aria-labelledby="cart-title">
        <div class="cart-header">
            <div>
                <p class="cart-title" id="cart-title">Your Cart</p>
                <p class="cart-subtitle">Ready when you are.</p>
            </div>
            <button class="cart-close" type="button" aria-label="Close cart" data-cart-close>X</button>
        </div>
        <div class="cart-body">
            <div class="cart-empty" data-cart-empty>
                <div class="cart-empty-icon">Cart</div>
                <p class="cart-empty-title">Your cart is empty</p>
                <p class="cart-empty-text">Find something you love, then come back here to checkout.</p>
                <a class="btn-outline" href="{{ route('shop') }}">Browse the shop</a>
            </div>
            <ul class="cart-items" data-cart-items></ul>
        </div>
        <div class="cart-summary">
            <div class="cart-row">
                <span>Subtotal</span>
                <span data-cart-subtotal>KSh 0.00</span>
            </div>
            <div class="cart-row">
                <span>Delivery</span>
                <span class="text-slate-500">Calculated at checkout</span>
            </div>
            <div class="cart-row cart-total">
                <span>Estimated total</span>
                <span data-cart-total>KSh 0.00</span>
            </div>
            <div class="cart-actions">
                <a class="btn-brand w-full justify-center" href="{{ route('checkout') }}">Checkout</a>
                <button class="btn-outline w-full justify-center" type="button" data-cart-close>Continue shopping</button>
            </div>
        </div>
    </div>
</div>
