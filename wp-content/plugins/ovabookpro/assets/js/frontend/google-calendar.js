
const CLIENT_ID = obp_google_calendar?.client_id;
const API_KEY = obp_google_calendar?.api_key;

// Discovery doc URL for APIs used by the quickstart
const DISCOVERY_DOC = obp_google_calendar?.discovery_doc;

// Authorization scopes required by the API; multiple scopes can be
// included, separated by spaces.
const SCOPES = obp_google_calendar?.scopes;

let tokenClient;
let gapiInited = false;
let gisInited = false;

/**
* Callback after api.js is loaded.
*/
function gapiLoaded() {
	gapi.load('client', initializeGapiClient);
}

/**
	* Callback after the API client is loaded. Loads the
	* discovery doc to initialize the API.
*/
async function initializeGapiClient() {
		await gapi.client.init({
		apiKey: API_KEY,
		discoveryDocs: [DISCOVERY_DOC],
	});
	gapiInited = true;
}


/**
* Callback after Google Identity Services are loaded.
*/
function gisLoaded() {
	tokenClient = google.accounts.oauth2.initTokenClient({
		client_id: CLIENT_ID,
		scope: SCOPES,
		callback: '', // defined later
	});

	gisInited = true;
}
