// Supabase Configuration
// REPLACE THESE WITH YOUR OWN SUPABASE PROJECT DETAILS
// You can find them in Supabase Dashboard -> Project Settings -> API
const SUPABASE_URL = 'https://ecljdstmedvnfsxbiwkf.supabase.co';
const SUPABASE_ANON_KEY = 'sb_publishable_UG7DuQihHJbIbOL39rsDHQ_uMXaPYF0';

// Initialize Supabase Client
// This assumes the Supabase JS library is loaded via CDN in the HTML file
// <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

let supabaseClient;

if (typeof supabase !== 'undefined') {
    supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
} else {
    console.error('Supabase JS library not found. Make sure to include the CDN script.');
}
