-- Add more triggers for enhanced functionality

USE stock_trading_db;

DELIMITER //

-- Trigger 7: Audit User Updates
-- Log changes to user details into AuditLog
DROP TRIGGER IF EXISTS trg_audit_user_update//
CREATE TRIGGER trg_audit_user_update
AFTER UPDATE ON Users
FOR EACH ROW
BEGIN
    IF OLD.full_name != NEW.full_name OR 
       OLD.workplace != NEW.workplace OR 
       OLD.region_id != NEW.region_id THEN
       
        INSERT INTO AuditLog (user_id, action_type, table_name, record_id, old_values, new_values)
        VALUES (NEW.user_id, 'UPDATE', 'Users', NEW.user_id,
                JSON_OBJECT('full_name', OLD.full_name, 'workplace', OLD.workplace, 'region_id', OLD.region_id),
                JSON_OBJECT('full_name', NEW.full_name, 'workplace', NEW.workplace, 'region_id', NEW.region_id));
    END IF;
END//

-- Trigger 8: Validate Ticker Symbol
-- Force ticker symbols to be uppercase before insert
DROP TRIGGER IF EXISTS trg_validate_ticker_symbol//
CREATE TRIGGER trg_validate_ticker_symbol
BEFORE INSERT ON Stock
FOR EACH ROW
BEGIN
    SET NEW.ticker_symbol = UPPER(NEW.ticker_symbol);
END//

-- Trigger 9: Prevent Future Business Year
-- Ensure year_established is not in the future
DROP TRIGGER IF EXISTS trg_prevent_future_business//
CREATE TRIGGER trg_prevent_future_business
BEFORE INSERT ON Business
FOR EACH ROW
BEGIN
    IF NEW.year_established > YEAR(CURDATE()) THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Business establishment year cannot be in the future';
    END IF;
END//

-- Trigger 10: Limit Watchlist Items
-- Prevent a user from having more than 20 items in their watchlist
DROP TRIGGER IF EXISTS trg_limit_watchlist//
CREATE TRIGGER trg_limit_watchlist
BEFORE INSERT ON Watchlist
FOR EACH ROW
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) INTO v_count
    FROM Watchlist
    WHERE user_id = NEW.user_id;
    
    IF v_count >= 20 THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Watchlist limit reached (max 20 items)';
    END IF;
END//

-- Trigger 11: Cleanup Old Notifications
-- Keep only the last 50 notifications for a user
DROP TRIGGER IF EXISTS trg_cleanup_notifications//
CREATE TRIGGER trg_cleanup_notifications
AFTER INSERT ON Notification
FOR EACH ROW
BEGIN
    DECLARE v_count INT;
    
    SELECT COUNT(*) INTO v_count
    FROM Notification
    WHERE user_id = NEW.user_id;
    
    IF v_count > 50 THEN
        DELETE FROM Notification
        WHERE notification_id IN (
            SELECT notification_id FROM (
                SELECT notification_id
                FROM Notification
                WHERE user_id = NEW.user_id
                ORDER BY created_at ASC
                LIMIT 1
            ) as tmp
        );
    END IF;
END//

DELIMITER ;
