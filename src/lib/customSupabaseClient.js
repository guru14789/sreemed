import { createClient } from '@supabase/supabase-js';

const supabaseUrl = 'https://krgsjqeqypbjpejjvmuh.supabase.co';
const supabaseAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImtyZ3NqcWVxeXBianBlamp2bXVoIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NTExMjUyNTcsImV4cCI6MjA2NjcwMTI1N30.Bq4It-YYVMxHJfsZ-yUt2Nz91NY2-tjqL1Kze4xSysE';

export const supabase = createClient(supabaseUrl, supabaseAnonKey);