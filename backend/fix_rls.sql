-- FIX RLS INFINITE RECURSION
-- Run this in Supabase Dashboard -> SQL Editor

-- 1. Create a secure function to check if the user is an admin
-- 'SECURITY DEFINER' means this function runs with the privileges of the creator (postgres/admin),
-- bypassing RLS on the table it queries.
CREATE OR REPLACE FUNCTION public.is_admin()
RETURNS BOOLEAN
LANGUAGE sql
SECURITY DEFINER
AS $$
  SELECT EXISTS (
    SELECT 1
    FROM public.users
    WHERE auth_id = auth.uid()
    AND role = 'admin'
  );
$$;

-- 2. Drop existing failing policies
DROP POLICY IF EXISTS "Admins can view all profiles" ON public.users;
DROP POLICY IF EXISTS "Admins can update users" ON public.users;
DROP POLICY IF EXISTS "Admins can delete users" ON public.users;
DROP POLICY IF EXISTS "Admins can update settings" ON public.settings;

-- 3. Re-create policies using the secure function

-- Admins can view all profiles
CREATE POLICY "Admins can view all profiles"
ON public.users FOR SELECT
USING ( public.is_admin() );

-- Admins can update users
CREATE POLICY "Admins can update users"
ON public.users FOR UPDATE
USING ( public.is_admin() );

-- Admins can delete users
CREATE POLICY "Admins can delete users"
ON public.users FOR DELETE
USING ( public.is_admin() );

-- Settings: Admins can update
CREATE POLICY "Admins can update settings"
ON public.settings FOR UPDATE
USING ( public.is_admin() );

-- 4. Allow Pre-registration (Insert without auth_id)
-- We need to allow insert if the user is an admin (creating a user manually)
-- OR if the user is signing up (auth.uid() matches).
DROP POLICY IF EXISTS "Users can insert their own profile" ON public.users;

CREATE POLICY "Allow Insert for Registration or Admin"
ON public.users FOR INSERT
WITH CHECK (
  (auth.uid() = auth_id) -- User registering themselves
  OR
  (public.is_admin())    -- Admin creating a user profile
);
