<x-storefront-layout :businessDetail="$businessDetail" :cart="$cart" title="Checkout">
    <div class="py-8 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-extrabold text-secondary-custom mb-8 text-center">Checkout</h1>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Checkout Form -->
                <div class="lg:col-span-2">
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="text-lg font-medium text-secondary-custom">Shipping Information</h2>
                        </div>

                        <form action="{{ route('storefront.checkout.process', $businessDetail->store_slug) }}" method="POST" class="px-6 py-4">
                            @csrf

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Full Name</label>
                                    <input type="text" id="name" name="name" value="{{ old('name', $client->name ?? '') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">
                                    @error('name')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                                    <input type="email" id="email" name="email" value="{{ old('email', $client->email ?? '') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">
                                    @error('email')
                                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-6">
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone Number</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $client->phone ?? '') }}" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">
                                @error('phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="shipping_address" class="block text-sm font-medium text-gray-700 mb-1">Shipping Address</label>
                                <textarea id="shipping_address" name="shipping_address" rows="3" required class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">{{ old('shipping_address', $client->address ?? '') }}</textarea>
                                @error('shipping_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input id="same_billing" name="same_billing" type="checkbox" checked class="h-4 w-4 text-primary-custom focus:ring-primary-custom border-gray-300 rounded">
                                    <label for="same_billing" class="ml-2 block text-sm text-gray-700">
                                        Billing address is the same as shipping address
                                    </label>
                                </div>
                            </div>

                            <div id="billing_address_container" class="hidden mb-6">
                                <label for="billing_address" class="block text-sm font-medium text-gray-700 mb-1">Billing Address</label>
                                <textarea id="billing_address" name="billing_address" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">{{ old('billing_address') }}</textarea>
                                @error('billing_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="mb-6">
                                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Order Notes (Optional)</label>
                                <textarea id="notes" name="notes" rows="3" class="block w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-custom focus:border-primary-custom sm:text-sm">{{ old('notes') }}</textarea>
                                @error('notes')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="border-t border-gray-200 pt-6">
                                <h3 class="text-lg font-medium text-secondary-custom mb-4">Payment Method</h3>

                                @if($businessDetail->payment_enabled)
                                    <div class="mb-6">
                                        <div class="space-y-4">
                                            <div class="flex items-center">
                                                <input id="payment_method_online" name="payment_method" type="radio" value="online" checked class="h-4 w-4 text-primary-custom focus:ring-primary-custom border-gray-300">
                                                <label for="payment_method_online" class="ml-3 block text-sm font-medium text-gray-700">
                                                    Pay Online
                                                    <span class="text-xs text-gray-500 block mt-1">
                                                        Secure payment via
                                                        @if($businessDetail->default_payment_gateway === 'paystack')
                                                            Paystack
                                                        @elseif($businessDetail->default_payment_gateway === 'flutterwave')
                                                            Flutterwave
                                                        @elseif($businessDetail->default_payment_gateway === 'stripe')
                                                            Stripe
                                                        @else
                                                            our payment processor
                                                        @endif
                                                    </span>
                                                </label>
                                            </div>

                                            <div class="flex items-center">
                                                <input id="payment_method_cod" name="payment_method" type="radio" value="cod" class="h-4 w-4 text-primary-custom focus:ring-primary-custom border-gray-300">
                                                <label for="payment_method_cod" class="ml-3 block text-sm font-medium text-gray-700">
                                                    Cash on Delivery
                                                    <span class="text-xs text-gray-500 block mt-1">Pay when you receive your order</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <p class="text-sm text-gray-500 mb-4">Payment will be collected upon delivery or in-store pickup.</p>
                                    <input type="hidden" name="payment_method" value="cod">
                                @endif

                                <button type="submit" class="w-full flex justify-center items-center px-6 py-3 border border-transparent rounded-md shadow-sm text-base font-medium text-white bg-accent-custom hover:bg-opacity-90">
                                    @if($businessDetail->payment_enabled)
                                        <span id="button_text_online">Proceed to Payment</span>
                                        <span id="button_text_cod" class="hidden">Place Order</span>
                                    @else
                                        Place Order
                                    @endif
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Order Summary -->
                <div class="lg:col-span-1">
                    <div class="bg-white shadow-md rounded-lg overflow-hidden">
                        <div class="border-b border-gray-200 px-6 py-4">
                            <h2 class="text-lg font-medium text-secondary-custom">Order Summary</h2>
                        </div>

                        <div class="px-6 py-4">
                            <ul role="list" class="divide-y divide-gray-200 mb-6">
                                @foreach($cart->items as $item)
                                    <li class="py-4 flex">
                                        <div class="h-16 w-16 flex-shrink-0 overflow-hidden rounded-md border border-gray-200">
                                            @if($item->product->primary_image)
                                                <img src="{{ Storage::url($item->product->primary_image) }}" alt="{{ $item->product->name }}" class="h-full w-full object-cover object-center">
                                            @else
                                                <div class="h-full w-full flex items-center justify-center bg-gray-200">
                                                    <svg class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                    </svg>
                                                </div>
                                            @endif
                                        </div>

                                        <div class="ml-4 flex-1 flex flex-col">
                                            <div>
                                                <div class="flex justify-between text-sm font-medium text-gray-900">
                                                    <h3>{{ $item->product->name }}</h3>
                                                    <p class="ml-4">{{ $currencySymbol }}{{ number_format($item->price * $item->quantity, 2) }}</p>
                                                </div>
                                                <p class="mt-1 text-sm text-gray-500">Qty: {{ $item->quantity }}</p>

                                                @if(!empty($item->options))
                                                    <div class="mt-1 text-xs text-gray-500">
                                                        @foreach($item->options as $key => $value)
                                                            <span class="mr-2">{{ ucfirst($key) }}: {{ $value }}</span>
                                                        @endforeach
                                                    </div>
                                                @endif

                                                @if($item->product->is_custom_order)
                                                    <p class="mt-1 text-xs text-primary-custom">Custom Design</p>
                                                @endif
                                            </div>
                                        </div>
                                    </li>
                                @endforeach
                            </ul>

                            <div class="flex justify-between py-2">
                                <span class="text-gray-600">Subtotal</span>
                                <span class="font-medium">{{ $currencySymbol }}{{ number_format($cart->subtotal ?? $cart->total, 2) }}</span>
                            </div>

                            <div class="flex justify-between py-2 border-t border-gray-200">
                                <span class="text-gray-600">Shipping</span>
                                <span class="font-medium">Calculated after order</span>
                            </div>

                            <div class="flex justify-between py-2 border-t border-gray-200">
                                <span class="text-gray-600">Tax</span>
                                @if(isset($cart->tax_amount) && $cart->tax_amount > 0)
                                    <span class="font-medium">{{ $currencySymbol }}{{ number_format($cart->tax_amount, 2) }}</span>
                                @else
                                    <span class="font-medium">Calculated after order</span>
                                @endif
                            </div>

                            <div class="flex justify-between py-2 border-t border-gray-200 text-lg font-bold">
                                <span class="text-gray-900">Total</span>
                                <span class="text-accent-custom">{{ $currencySymbol }}{{ number_format($cart->total, 2) }}</span>
                            </div>

                            <div class="mt-6">
                                <a href="{{ route('storefront.cart', $businessDetail->store_slug) }}" class="text-sm font-medium text-primary-custom hover:text-secondary-custom">
                                    &larr; Back to Cart
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sameBillingCheckbox = document.getElementById('same_billing');
            const billingAddressContainer = document.getElementById('billing_address_container');

            sameBillingCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    billingAddressContainer.classList.add('hidden');
                } else {
                    billingAddressContainer.classList.remove('hidden');
                }
            });

            // Payment method toggle
            const paymentMethodOnline = document.getElementById('payment_method_online');
            const paymentMethodCod = document.getElementById('payment_method_cod');
            const buttonTextOnline = document.getElementById('button_text_online');
            const buttonTextCod = document.getElementById('button_text_cod');

            if (paymentMethodOnline && paymentMethodCod) {
                paymentMethodOnline.addEventListener('change', function() {
                    if (this.checked) {
                        buttonTextOnline.classList.remove('hidden');
                        buttonTextCod.classList.add('hidden');
                    }
                });

                paymentMethodCod.addEventListener('change', function() {
                    if (this.checked) {
                        buttonTextOnline.classList.add('hidden');
                        buttonTextCod.classList.remove('hidden');
                    }
                });
            }
        });
    </script>
</x-storefront-layout>
