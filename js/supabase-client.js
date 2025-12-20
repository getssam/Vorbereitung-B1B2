// Supabase Configuration
// REPLACE THESE WITH YOUR OWN SUPABASE PROJECT DETAILS
// You can find them in Supabase Dashboard -> Project Settings -> API
const SUPABASE_URL = 'https://ecljdstmedvnfsxbiwkf.supabase.co';
const SUPABASE_ANON_KEY = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImVjbGpkc3RtZWR2bmZzeGJpd2tmIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NjYyMTU3MDUsImV4cCI6MjA4MTc5MTcwNX0.Xj_rSUxKg3lCPrZouAhOe0vPuHduPXza7-EGiMT_ujM';

// Initialize Supabase Client
// This assumes the Supabase JS library is loaded via CDN in the HTML file
// <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js@2"></script>

let supabaseClient;

if (typeof supabase !== 'undefined') {
    supabaseClient = supabase.createClient(SUPABASE_URL, SUPABASE_ANON_KEY);
} else {
    console.error('Supabase JS library not found. Make sure to include the CDN script.');
}
