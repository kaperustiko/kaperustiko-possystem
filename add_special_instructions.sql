-- SQL to add special_instructions column to the orders table
ALTER TABLE orders ADD COLUMN special_instructions TEXT DEFAULT NULL; 