-- Auto-generated from joins-mysql.yaml (map@sha1:DA70105A5B799F72A56FEAB71A5171F946A770D2)
-- engine: mysql
-- view:   orders_funnel

CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY INVOKER VIEW vw_orders_funnel AS
SELECT
  COUNT(*) AS orders_total,
  SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
  SUM(CASE WHEN status = 'paid'      THEN 1 ELSE 0 END) AS paid,
  SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) AS completed,
  SUM(CASE WHEN status = 'failed'    THEN 1 ELSE 0 END) AS failed,
  SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) AS cancelled,
  SUM(CASE WHEN status = 'refunded'  THEN 1 ELSE 0 END) AS refunded,
  ROUND(
    100.0 * SUM(CASE WHEN status IN ('paid','completed') THEN 1 ELSE 0 END) / NULLIF(COUNT(*), 0),
    2
  ) AS payment_conversion_pct
FROM orders;

-- Auto-generated from joins-mysql.yaml (map@sha1:DA70105A5B799F72A56FEAB71A5171F946A770D2)
-- engine: mysql
-- view:   orders_payments_latest

CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY INVOKER VIEW vw_orders_payments_latest AS
WITH ranked_payments AS (
  SELECT
    p.*,
    ROW_NUMBER() OVER (PARTITION BY p.tenant_id, p.order_id ORDER BY p.created_at DESC, p.id DESC) AS rn
  FROM payments p
)
SELECT
  o.id          AS order_id,
  o.tenant_id,
  o.user_id,
  o.status      AS order_status,
  o.total       AS order_total,
  o.currency    AS order_currency,
  rp.gateway    AS payment_gateway,
  rp.status     AS payment_status,
  rp.amount     AS payment_amount,
  rp.currency   AS payment_currency,
  rp.created_at AS payment_created_at
FROM orders o
LEFT JOIN ranked_payments rp
  ON rp.tenant_id = o.tenant_id
 AND rp.order_id  = o.id
 AND rp.rn = 1;


-- Auto-generated from joins-mysql.yaml (map@sha1:DA70105A5B799F72A56FEAB71A5171F946A770D2)
-- engine: mysql
-- view:   orders_user_summary

CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY INVOKER VIEW vw_orders_user_summary AS
SELECT
  u.id AS user_id,
  COUNT(o.id) AS orders_count,
  SUM(CASE WHEN o.status IN ('paid','completed') THEN o.total ELSE 0 END) AS total_spent
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id;


-- Auto-generated from joins-mysql.yaml (map@sha1:DA70105A5B799F72A56FEAB71A5171F946A770D2)
-- engine: mysql
-- view:   orders_with_user

CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY INVOKER VIEW vw_orders_with_user AS
SELECT
  o.id,
  o.tenant_id,
  o.user_id,
  u.email_hash,
  o.status,
  o.total,
  o.currency,
  o.created_at,
  (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) AS items_count
FROM orders o
LEFT JOIN users u ON u.id = o.user_id;


-- Auto-generated from joins-mysql.yaml (map@sha1:DA70105A5B799F72A56FEAB71A5171F946A770D2)
-- engine: mysql
-- view:   revenue_daily

CREATE OR REPLACE ALGORITHM=TEMPTABLE SQL SECURITY INVOKER VIEW vw_revenue_daily AS
SELECT
  DATE(created_at) AS day,
  SUM(CASE WHEN status IN ('paid','completed') THEN 1 ELSE 0 END) AS paid_orders,
  SUM(CASE WHEN status IN ('paid','completed') THEN total ELSE 0 END) AS revenue_gross,
  SUM(CASE WHEN status IN ('failed','cancelled') THEN 1 ELSE 0 END) AS lost_orders,
  SUM(CASE WHEN status IN ('failed','cancelled') THEN total ELSE 0 END) AS lost_total
FROM orders
GROUP BY DATE(created_at)
ORDER BY day DESC;

