import React, { useState, useEffect } from 'react';
import { Head } from '@inertiajs/react';
import axios from 'axios';

const Test = ({ integrations }) => {
    const [shop, setShop] = useState('');
    const [apiKey, setApiKey] = useState('');
    const [apiSecret, setApiSecret] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    // Check for success/error messages from redirect
    useEffect(() => {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) setSuccess(true);
        if (urlParams.has('error')) setError(urlParams.get('error'));
    }, []);

    const handleConnect = (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        // Redirect to the backend install route with owner's specific credentials
        window.location.href = `/owner/shopify/install?shop=${shop}&api_key=${apiKey}&api_secret=${apiSecret}`;
    };

    return (
        <div style={{ backgroundColor: '#0f1117', minHeight: '100vh', color: 'white', padding: '40px', fontFamily: 'sans-serif' }}>
            <Head title="Connect Shopify Store" />

            <div style={{ maxWidth: '600px', margin: '0 auto' }}>
                <div style={{ textAlign: 'center', marginBottom: '40px' }}>
                    <h1 style={{ fontSize: '32px', fontWeight: 'bold', marginBottom: '10px', color: '#60a5fa' }}>
                        Shopify Owner Integration
                    </h1>
                    <p style={{ color: '#9ca3af', fontSize: '16px' }}>
                        Enter your own Custom App credentials to connect your store.
                    </p>
                </div>

                <div style={{ backgroundColor: '#161b22', borderRadius: '24px', padding: '40px', border: '1px solid #30363d', boxShadow: '0 20px 50px rgba(0,0,0,0.3)' }}>
                    
                    {/* Redirect URL Info Section */}
                    <div style={{ marginBottom: '30px', backgroundColor: 'rgba(255, 193, 7, 0.05)', padding: '20px', borderRadius: '16px', border: '1px solid rgba(255, 193, 7, 0.2)' }}>
                        <h4 style={{ margin: '0 0 10px 0', color: '#ffc107', fontSize: '14px', fontWeight: 'bold', textTransform: 'uppercase' }}>
                            Action Required: Whitelist Redirect URL
                        </h4>
                        <p style={{ margin: '0 0 12px 0', fontSize: '13px', color: '#9ca3af', lineHeight: '1.5' }}>
                            Copy the URL below and paste it into your Shopify App settings under <b>"Allowed redirection URL(s)"</b>:
                        </p>
                        <div style={{ backgroundColor: '#0d1117', padding: '12px', borderRadius: '8px', border: '1px solid #30363d', fontFamily: 'monospace', fontSize: '13px', color: '#34d399', wordBreak: 'break-all' }}>
                            http://localhost:8000/owner/shopify/callback
                        </div>
                    </div>

                    <form onSubmit={handleConnect} style={{ display: 'flex', flexDirection: 'column', gap: '20px' }}>
                        <div>
                            <label style={{ display: 'block', fontSize: '14px', fontWeight: 'bold', color: '#60a5fa', marginBottom: '8px' }}>
                                Shopify Store URL
                            </label>
                            <input
                                type="text"
                                value={shop}
                                onChange={(e) => setShop(e.target.value)}
                                placeholder="example.myshopify.com"
                                style={{ width: '100%', backgroundColor: '#0d1117', border: '1px solid #30363d', borderRadius: '12px', padding: '15px', color: 'white', boxSizing: 'border-box', outline: 'none' }}
                                required
                            />
                        </div>

                        <div>
                            <label style={{ display: 'block', fontSize: '14px', fontWeight: 'bold', color: '#60a5fa', marginBottom: '8px' }}>
                                Client ID (API Key)
                            </label>
                            <input
                                type="text"
                                value={apiKey}
                                onChange={(e) => setApiKey(e.target.value)}
                                placeholder="Your Shopify App Client ID"
                                style={{ width: '100%', backgroundColor: '#0d1117', border: '1px solid #30363d', borderRadius: '12px', padding: '15px', color: 'white', boxSizing: 'border-box', outline: 'none' }}
                                required
                            />
                        </div>

                        <div>
                            <label style={{ display: 'block', fontSize: '14px', fontWeight: 'bold', color: '#60a5fa', marginBottom: '8px' }}>
                                Client Secret
                            </label>
                            <input
                                type="password"
                                value={apiSecret}
                                onChange={(e) => setApiSecret(e.target.value)}
                                placeholder="Your Shopify App Client Secret"
                                style={{ width: '100%', backgroundColor: '#0d1117', border: '1px solid #30363d', borderRadius: '12px', padding: '15px', color: 'white', boxSizing: 'border-box', outline: 'none' }}
                                required
                            />
                        </div>

                        {error && (
                            <div style={{ padding: '16px', backgroundColor: 'rgba(239, 68, 68, 0.1)', border: '1px solid rgba(239, 68, 68, 0.2)', color: '#f87171', borderRadius: '12px', fontSize: '14px' }}>
                                Error: {error}
                            </div>
                        )}

                        {success && (
                            <div style={{ padding: '16px', backgroundColor: 'rgba(16, 185, 129, 0.1)', border: '1px solid rgba(16, 185, 129, 0.2)', color: '#34d399', borderRadius: '12px', fontSize: '14px' }}>
                                Success! Shopify connected and token generated.
                            </div>
                        )}

                        <button
                            type="submit"
                            disabled={loading}
                            style={{
                                width: '100%',
                                padding: '18px',
                                borderRadius: '12px',
                                fontWeight: 'bold',
                                fontSize: '18px',
                                backgroundColor: loading ? '#374151' : '#3b82f6',
                                color: 'white',
                                border: 'none',
                                cursor: loading ? 'not-allowed' : 'pointer',
                                transition: 'all 0.2s',
                                marginTop: '10px'
                            }}
                        >
                            {loading ? 'Initiating OAuth...' : 'Generate Token & Connect'}
                        </button>
                    </form>
                </div>

                <div style={{ marginTop: '40px' }}>
                    <h2 style={{ fontSize: '20px', marginBottom: '20px' }}>Current Integrations</h2>
                    <div style={{ display: 'flex', flexDirection: 'column', gap: '12px' }}>
                        {integrations.length === 0 ? (
                            <p style={{ color: '#9ca3af' }}>No integrations found.</p>
                        ) : (
                            integrations.map((int) => (
                                <div key={int.id} style={{ backgroundColor: '#161b22', padding: '20px', borderRadius: '16px', border: '1px solid #30363d', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                                    <div>
                                        <div style={{ fontWeight: 'bold', fontSize: '18px' }}>{int.provider_id}</div>
                                        <div style={{ color: '#9ca3af', fontSize: '14px' }}>{int.provider}</div>
                                    </div>
                                    <div style={{ backgroundColor: int.status === 'connected' ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)', color: int.status === 'connected' ? '#10b981' : '#f87171', padding: '6px 12px', borderRadius: '20px', fontSize: '12px', fontWeight: 'bold' }}>
                                        {int.status.toUpperCase()}
                                    </div>
                                </div>
                            ))
                        )}
                    </div>
                </div>
                
                {integrations.some(i => i.status === 'connected') && (
                    <div style={{ marginTop: '30px', textAlign: 'center' }}>
                        <a 
                            href="/owner/shopify/customers" 
                            style={{ color: '#60a5fa', textDecoration: 'none', fontWeight: 'bold', border: '1px solid #60a5fa', padding: '10px 20px', borderRadius: '12px' }}
                        >
                            View Fetched Customers
                        </a>
                    </div>
                )}
            </div>
        </div>
    );
};

export default Test;
