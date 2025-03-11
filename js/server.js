// oauth_example.js
const OAuth = require('oauth-1.0a');
const axios = require('axios');
const crypto = require('crypto');
const querystring = require('querystring');

// Replace with your own credentials
const consumerKey = '5efa84055c0d4df99ec83e0d603f59a0';
const consumerSecret = 'bba1e8b9df164d3190f5ce9273087e96';

// Initialize OAuth 1.0a client
const oauth = OAuth({
  consumer: { key: consumerKey, secret: consumerSecret },
  signature_method: 'HMAC-SHA1',
  hash_function(base_string, key) {
    return crypto.createHmac('sha1', key).update(base_string).digest('base64');
  },
});

// Step 1: Get Request Token
async function getRequestToken() {
  const requestData = {
    url: 'https://platform.fatsecret.com/rest/server.api',
    method: 'POST',
    data: {
      oauth_callback: 'YOUR_CALLBACK_URL', // Your callback URL
      method: 'oauth.request_token',
    },
  };

  try {
    const response = await axios({
      url: requestData.url,
      method: requestData.method,
      params: oauth.authorize(requestData),
    });
    const responseParams = querystring.parse(response.data);
    console.log('Request Token:', responseParams);
    return responseParams;
  } catch (error) {
    console.error('Error getting request token:', error);
  }
}

// Step 2: Redirect user for authorization
function getAuthorizationUrl(requestToken) {
  const authUrl = `https://platform.fatsecret.com/oauth/authorize?oauth_token=${requestToken.oauth_token}`;
  console.log('Redirect the user to this URL:', authUrl);
  // Redirect the user to the authorization URL
}

// Step 3: After the user authorizes, get Access Token
async function getAccessToken(requestToken, oauthVerifier) {
  const requestData = {
    url: 'https://platform.fatsecret.com/rest/server.api',
    method: 'POST',
    data: {
      oauth_token: requestToken.oauth_token,
      oauth_verifier: oauthVerifier,
      method: 'oauth.access_token',
    },
  };

  try {
    const response = await axios({
      url: requestData.url,
      method: requestData.method,
      params: oauth.authorize(requestData),
    });
    const responseParams = querystring.parse(response.data);
    console.log('Access Token:', responseParams);
    return responseParams;
  } catch (error) {
    console.error('Error getting access token:', error);
  }
}

// Run this process
async function startOAuth() {
  const requestToken = await getRequestToken();
  getAuthorizationUrl(requestToken);

  // After user has authorized, pass the oauth_verifier here
  const oauthVerifier = 'user_oauth_verifier'; // From the URL callback
  const accessToken = await getAccessToken(requestToken, oauthVerifier);
  console.log('Final Access Token:', accessToken);
}

startOAuth();
