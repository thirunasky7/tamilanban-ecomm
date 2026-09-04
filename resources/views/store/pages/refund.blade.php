<x-static-page :title="__('store.refund_policy')">
<p class="msh-static-updated">Last updated: {{ date('F j, Y') }}</p>

<p>At ShopEase, we want you to shop with confidence. This policy outlines how cancellations, returns, and refunds are handled.</p>

<h2>Order Cancellation</h2>
<ul>
<li><strong>Before dispatch:</strong> You may cancel your order from My Orders or by contacting support within 24 hours of placing it.</li>
<li><strong>After dispatch:</strong> Cancellation may not be possible once the order is shipped. You may refuse delivery or initiate a return after delivery.</li>
<li><strong>COD orders:</strong> Cancel before dispatch at no charge. No cancellation fee applies for eligible pre-shipment cancellations.</li>
</ul>

<h2>Return Eligibility</h2>
<p>Returns are accepted within <strong>7 days</strong> of delivery for eligible products, subject to the following conditions:</p>
<ul>
<li>Product is unused, unworn, and in original packaging with tags intact.</li>
<li>Electronics and sealed items must be unopened unless defective.</li>
<li>Perishable goods (masala, food items) are non-returnable unless damaged or expired on arrival.</li>
<li>Innerwear, customized, and clearance sale items are non-returnable.</li>
</ul>

<h2>Non-Returnable Items</h2>
<ul>
<li>Products marked as non-returnable on the product page.</li>
<li>Items damaged due to misuse or normal wear after delivery.</li>
<li>Products without original invoice, tags, or accessories.</li>
</ul>

<h2>How to Request a Return or Refund</h2>
<ol>
<li>Log in and go to <a href="{{ route('orders.index') }}">My Orders</a>, or contact us via the <a href="{{ route('pages.contact') }}">Contact page</a>.</li>
<li>Provide your order number, reason for return, and photos if the item is damaged or incorrect.</li>
<li>Our team will confirm eligibility and arrange pickup or provide return instructions.</li>
<li>After inspection, approved refunds are processed within 5–7 business days.</li>
</ol>

<h2>Refund Method</h2>
<ul>
<li><strong>Online payments (UPI / Net Banking):</strong> Refund credited to the original payment method via Razorpay.</li>
<li><strong>Cash on Delivery:</strong> Refund issued via bank transfer or UPI to your registered mobile number.</li>
<li>Shipping charges are non-refundable unless the return is due to our error or a defective product.</li>
</ul>

<h2>Exchanges</h2>
<p>Exchanges are offered for size or variant issues on eligible apparel items, subject to stock availability. Contact support within 7 days of delivery.</p>

<h2>Damaged or Wrong Items</h2>
<p>If you receive a damaged, defective, or incorrect product, notify us within 48 hours of delivery with photos. We will arrange a free replacement or full refund including shipping.</p>

<h2>Refund Timeline</h2>
<p>Once your return is received and approved, refunds are initiated within 5–7 business days. Bank processing may take an additional 3–5 business days depending on your provider.</p>

<h2>Contact Us</h2>
<p>For cancellation or refund assistance, email <a href="mailto:support@shopease.test">support@shopease.test</a> or use our <a href="{{ route('pages.contact') }}">Contact page</a>.</p>
</x-static-page>
