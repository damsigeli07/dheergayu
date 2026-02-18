-- Remove supplier column from batches table
ALTER TABLE batches DROP COLUMN supplier;

-- If you have an expired_batches table, run this as well:
-- ALTER TABLE expired_batches DROP COLUMN supplier;
