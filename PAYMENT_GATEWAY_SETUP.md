# Real Payment Gateway Integration Guide

## Choose Your Payment Gateway

### Option 1: Stripe (Recommended for most regions)
- Works globally including Sri Lanka
- Supports card, Apple Pay, Google Pay
- Sandbox for testing available
- Setup: 15-20 minutes

### Option 2: Razorpay (Best for South Asia)
- Optimized for India, Sri Lanka, Bangladesh
- Lower fees for local payments
- UPI support for India
- Setup: 15-20 minutes

### Option 3: PayPal
- Global coverage
- Complex setup
- Not ideal for small projects

---

## Implementation: Stripe (Step-by-Step)

### Step 1: Create Stripe Account

1. Go to https://stripe.com
2. Sign up (free test account)
3. Go to Dashboard → API keys
4. Copy:
   - **Publishable Key** (starts with `pk_test_`)
   - **Secret Key** (starts with `sk_test_`) - Keep this safe!

### Step 2: Install Stripe PHP Library

Run in terminal:
```bash
cd c:\wamp64\www\New folder (2)\New-folder\backend
composer require stripe/stripe-php
```

If composer not installed, use:
```bash
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
php composer-setup.php
php composer install
```

### Step 3: Create Backend Payment Endpoint

Create file: `backend/payments/create-checkout.php`

```php
<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

require_once __DIR__ . '/vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY_HERE');

try {
    $input = json_decode(file_get_contents('php://input'), true);
    
    $cartItems = $input['cartItems'] ?? [];
    $subtotal = $input['subtotal'] ?? 0;
    $shipping = $input['shipping'] ?? 0;
    $total = $input['total'] ?? 0;
    
    // Create line items for Stripe
    $lineItems = [];
    foreach ($cartItems as $item) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => $item['name'],
                    'images' => [$item['image'] ?? ''],
                ],
                'unit_amount' => intval($item['price'] * 100), // Convert to cents
            ],
            'quantity' => $item['quantity'],
        ];
    }
    
    // Add shipping as a line item
    if ($shipping > 0) {
        $lineItems[] = [
            'price_data' => [
                'currency' => 'usd',
                'product_data' => [
                    'name' => 'Shipping',
                ],
                'unit_amount' => intval($shipping * 100),
            ],
            'quantity' => 1,
        ];
    }
    
    // Create Stripe checkout session
    $session = \Stripe\Checkout\Session::create([
        'payment_method_types' => ['card'],
        'line_items' => $lineItems,
        'mode' => 'payment',
        'success_url' => 'http://localhost/New%20folder%20(2)/New-folder/front/payment-success.html?session_id={CHECKOUT_SESSION_ID}',
        'cancel_url' => 'http://localhost/New%20folder%20(2)/New-folder/front/payment-cancel.html',
        'customer_email' => $input['email'] ?? '',
    ]);
    
    echo json_encode([
        'success' => true,
        'sessionId' => $session->id,
        'sessionUrl' => $session->url
    ]);
    
} catch (\Stripe\Exception\ApiErrorException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An error occurred: ' . $e->getMessage()
    ]);
}
?>
```

### Step 4: Create Backend Webhook Handler

Create file: `backend/payments/webhook.php`

```php
<?php
header('Content-Type: application/json');

require_once __DIR__ . '/vendor/autoload.php';

\Stripe\Stripe::setApiKey('sk_test_YOUR_SECRET_KEY_HERE');

$endpointSecret = 'whsec_YOUR_WEBHOOK_SECRET_FROM_STRIPE_DASHBOARD';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';

try {
    $event = \Stripe\Webhook::constructEvent($payload, $sig_header, $endpointSecret);
} catch (\UnexpectedValueException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit();
} catch (\Stripe\Exception\SignatureVerificationException $e) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid signature']);
    exit();
}

// Handle event
switch ($event->type) {
    case 'checkout.session.completed':
        $session = $event->data->object;
        
        // Save order to database
        require_once __DIR__ . '/../config/database.php';
        $pdo = getDBConnection();
        
        $stmt = $pdo->prepare("
            INSERT INTO orders (
                stripe_session_id, 
                customer_email, 
                total_amount, 
                status, 
                created_at
            ) VALUES (?, ?, ?, ?, NOW())
        ");
        
        $stmt->execute([
            $session->id,
            $session->customer_email,
            $session->amount_total / 100, // Convert from cents
            'completed'
        ]);
        
        echo json_encode(['success' => true]);
        break;
        
    case 'payment_intent.payment_failed':
        // Handle failed payment
        echo json_encode(['success' => true]);
        break;
        
    default:
        echo json_encode(['success' => true]);
}
?>
```

### Step 5: Create Orders Database Table

```sql
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    stripe_session_id VARCHAR(255) UNIQUE,
    customer_email VARCHAR(255),
    total_amount DECIMAL(10, 2),
    status VARCHAR(50), -- completed, pending, failed
    items JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Step 6: Update Frontend Payment Modal

Update `front/script.js` payment section:

```javascript
async function processStripePayment() {
    const stripe = Stripe('pk_test_YOUR_PUBLISHABLE_KEY_HERE');
    
    // Collect cart data
    const cartItems = Array.from(state.cart.entries()).map(([productId, qty]) => {
        const product = products.find(p => p.id == productId);
        return {
            name: product.name,
            price: product.price,
            quantity: qty,
            image: product.image
        };
    });
    
    let subtotal = 0;
    state.cart.forEach((qty, productId) => {
        const product = products.find(p => p.id == productId);
        if (product) subtotal += product.price * qty;
    });
    
    const shipping = subtotal >= 200 ? 0 : 9.99;
    
    // Call backend to create checkout session
    try {
        const response = await fetch(
            'http://localhost/New%20folder%20(2)/New-folder/backend/payments/create-checkout.php',
            {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    cartItems,
                    subtotal,
                    shipping,
                    total: subtotal + shipping,
                    email: state.user?.email || ''
                })
            }
        );
        
        const data = await response.json();
        
        if (data.success) {
            // Redirect to Stripe checkout
            await stripe.redirectToCheckout({
                sessionId: data.sessionId
            });
        } else {
            showToast('Payment setup failed: ' + data.message);
        }
    } catch (error) {
        console.error('Payment error:', error);
        showToast('Payment processing error');
    }
}

// In payment form submit handler:
if (paymentForm) {
    paymentForm.addEventListener('submit', (e) => {
        e.preventDefault();
        processStripePayment();
    });
}
```

### Step 7: Create Success and Cancel Pages

Create `front/payment-success.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Successful - BUY LK</title>
  <link rel="stylesheet" href="styles.css" />
  <style>
    .success-container {
      max-width: 600px;
      margin: 60px auto;
      padding: 40px;
      text-align: center;
      background: var(--surface);
      border-radius: 12px;
      box-shadow: var(--shadow);
    }
    .success-badge {
      font-size: 4rem;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>
  <div class="success-container">
    <div class="success-badge">✓</div>
    <h1>Payment Successful!</h1>
    <p>Your order has been placed successfully.</p>
    <p id="orderId">Order ID: <strong></strong></p>
    <button class="primary-btn" onclick="window.location.href='index.html'">
      Continue Shopping
    </button>
  </div>

  <script>
    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session_id');
    
    if (sessionId) {
      document.getElementById('orderId').innerHTML = 
        'Order ID: <strong>' + sessionId.substring(0, 20) + '...</strong>';
    }
  </script>
</body>
</html>
```

Create `front/payment-cancel.html`:

```html
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Payment Cancelled - BUY LK</title>
  <link rel="stylesheet" href="styles.css" />
</head>
<body>
  <div style="max-width: 600px; margin: 60px auto; padding: 40px; text-align: center; background: var(--surface); border-radius: 12px;">
    <h1>Payment Cancelled</h1>
    <p>Your payment was not completed. Your cart items are still saved.</p>
    <button class="primary-btn" onclick="window.location.href='shop.html'">
      Return to Shopping
    </button>
  </div>
</body>
</html>
```

---

## Testing Stripe Locally

### Test Card Numbers:
- **Success**: 4242 4242 4242 4242
- **Decline**: 4000 0000 0000 0002
- Expiry: Any future date (e.g., 12/25)
- CVC: Any 3 digits (e.g., 123)

### Test Webhook Locally:

1. Install Stripe CLI from https://stripe.com/docs/stripe-cli
2. Run: `stripe listen --forward-to localhost/webhook`
3. Copy webhook signing secret and add to `webhook.php`

---

## Alternative: Razorpay (for South Asia)

**Advantages:**
- Better for Sri Lanka/India
- Lower fees
- UPI support
- Easier approval

**Setup Steps:**
1. Create account at https://razorpay.com
2. Get Key ID and Key Secret
3. Use similar flow with Razorpay PHP SDK
4. Webhook handling similar to Stripe

---

## Security Checklist

- [ ] Use Stripe in Test mode first
- [ ] Never commit API keys to git
- [ ] Use environment variables for keys
- [ ] Validate amounts on backend
- [ ] Implement webhook verification
- [ ] Store orders in database
- [ ] Log all transactions
- [ ] Use HTTPS in production
- [ ] Implement PCI compliance

---

## Summary

**Current**: Mock payment with setTimeout
**After Implementation**: Real Stripe/Razorpay payments with:
- Secure checkout
- Order database
- Webhook confirmations
- Email receipts
- Admin order management

Would you like me to implement these files into your project?
