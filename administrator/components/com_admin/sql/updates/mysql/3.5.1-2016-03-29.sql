--
-- Reset UTF-8 Multibyte (utf8mb4) or UTF-8 conversion status
-- to force a new conversion when updating from a version lower
-- than 3.5.1.
--

UPDATE `#__utf8_conversion` SET `converted` = 0;
