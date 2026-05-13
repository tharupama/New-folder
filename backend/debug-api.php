<?php
/**
 * Debug script to test the subscription API directly
 */

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Subscription API Debug</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 20px auto;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 { color: #333; border-bottom: 3px solid #667eea; padding-bottom: 10px; }
        h2 { color: #555; margin-top: 20px; }
        .form-group {
            margin: 15px 0;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input, textarea {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: monospace;
            box-sizing: border-box;
        }
        textarea {
            height: 150px;
            resize: vertical;
        }
        button {
            background: #667eea;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        button:hover {
            background: #764ba2;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .success {
            background: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }
        .error {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
        }
        .info {
            background: #dbeafe;
            border: 2px solid #3b82f6;
            color: #1e40af;
        }
        .step {
            background: #fef3c7;
            border: 2px solid #f59e0b;
            padding: 15px;
            margin: 15px 0;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 Subscription API Debug Tool</h1>
        
        <div class="step">
            <h3>📋 Test Steps:</h3>
            <ol>
                <li>Fill in the email address</li>
                <li>Click "Test Subscribe"</li>
                <li>Check the response below</li>
                <li>Look for any errors in the response</li>
            </ol>
        </div>

        <h2>Test Subscription Endpoint</h2>
        <form id="testForm">
            <div class="form-group">
                <label for="email">Email Address:</label>
                <input type="email" id="email" value="test@example.com" required>
            </div>
            <button type="submit">Test Subscribe</button>
        </form>

        <div id="result"></div>

        <h2>Direct Database Check</h2>
        <button onclick="checkDatabase()">Check Database</button>

    </div>

    <script>
        document.getElementById('testForm').addEventListener('submit', async (e) => {
            e.preventDefault();
            
            const email = document.getElementById('email').value;
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="info">⏳ Testing...</div>';
            
            try {
                console.log('Sending request to: ../backend/products/subscription.php');
                console.log('Email:', email);
                
                const response = await fetch('../backend/products/subscription.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ email: email })
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', {
                    'content-type': response.headers.get('content-type'),
                    'content-length': response.headers.get('content-length')
                });
                
                const text = await response.text();
                console.log('Response text:', text);
                
                let data;
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    resultDiv.innerHTML = `<div class="error">
                        <h3>❌ JSON Parse Error</h3>
                        <strong>Error:</strong> ${e.message}
                        <strong>Response:</strong> ${text.substring(0, 500)}
                    </div>`;
                    return;
                }
                
                if (response.ok && data.success) {
                    resultDiv.innerHTML = `<div class="success">
                        <h3>✅ Success!</h3>
                        <strong>Message:</strong> ${data.message}
                    </div>`;
                } else {
                    resultDiv.innerHTML = `<div class="error">
                        <h3>❌ Error</h3>
                        <strong>Status:</strong> ${response.status}
                        <strong>Message:</strong> ${data.message || 'Unknown error'}
                        <strong>Debug:</strong> ${data.debug || 'N/A'}
                    </div>`;
                }
            } catch (error) {
                resultDiv.innerHTML = `<div class="error">
                    <h3>❌ Request Error</h3>
                    <strong>Error:</strong> ${error.message}
                    <strong>Stack:</strong> ${error.stack}
                </div>`;
            }
        });
        
        function checkDatabase() {
            const resultDiv = document.getElementById('result');
            resultDiv.innerHTML = '<div class="info">⏳ Checking database...</div>';
            
            fetch('../setup-database.php')
                .then(r => r.text())
                .then(html => {
                    // Extract status info from setup page
                    const hasSuccess = html.includes('✅');
                    if (hasSuccess) {
                        resultDiv.innerHTML = `<div class="success">
                            <h3>✅ Database Setup Complete</h3>
                            <p>Visit: <a href="../setup-database.php" target="_blank">../backend/setup-database.php</a></p>
                        </div>`;
                    } else {
                        resultDiv.innerHTML = `<div class="error">
                            <h3>❌ Database Setup Issues</h3>
                            <p>Visit: <a href="../setup-database.php" target="_blank">../backend/setup-database.php</a></p>
                        </div>`;
                    }
                })
                .catch(e => {
                    resultDiv.innerHTML = `<div class="error">
                        <h3>❌ Error checking setup</h3>
                        <p>${e.message}</p>
                    </div>`;
                });
        }
    </script>
</body>
</html>
