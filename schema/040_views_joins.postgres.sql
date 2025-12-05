-- Auto-generated from joins-postgres.yaml (map@85230ed)
-- engine: postgres
-- view:   orders_with_user

-- Orders with user info and item counts
CREATE OR REPLACE VIEW vw_orders_with_user AS
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

-- Auto-generated from joins-postgres.yaml (map@85230ed)
-- engine: postgres
-- view:   orders_user_summary

-- User-level order summary
CREATE OR REPLACE VIEW vw_orders_user_summary AS
SELECT
  u.id AS user_id,
  COUNT(o.id) AS orders_count,
  SUM(CASE WHEN o.status IN ($$paid$$,$$completed$$) THEN o.total ELSE 0 END) AS total_spent
FROM users u
LEFT JOIN orders o ON o.user_id = u.id
GROUP BY u.id;


-- Auto-generated from joins-postgres.yaml (map@85230ed)
-- engine: postgres
-- view:   orders_payments_latest

-- Orders with latest payment snapshot
CREATE OR REPLACE VIEW vw_orders_payments_latest AS
SELECT
  o.id          AS order_id,
  o.tenant_id,
  o.user_id,
  o.status      AS order_status,
  o.total       AS order_total,
  o.currency    AS order_currency,
  p.gateway     AS payment_gateway,
  p.status      AS payment_status,
  p.amount      AS payment_amount,
  p.currency    AS payment_currency,
  p.created_at  AS payment_created_at
FROM orders o
LEFT JOIN LATERAL (
  SELECT *
  FROM payments p
  WHERE p.tenant_id = o.tenant_id AND p.order_id = o.id
  ORDER BY p.created_at DESC, p.id DESC
  LIMIT 1
) p ON TRUE;


-- Auto-generated from joins-postgres.yaml (map@85230ed)
-- engine: postgres
-- view:   orders_revenue_daily

-- Daily revenue (orders) and counts; refunds reported separately
CREATE OR REPLACE VIEW vw_revenue_daily AS
SELECT
  date_trunc($$day$$, created_at) AS day,
  COUNT(*) FILTER (WHERE status IN ($$paid$$,$$completed$$)) AS paid_orders,
  SUM(total) FILTER (WHERE status IN ($$paid$$,$$completed$$)) AS revenue_gross,
  COUNT(*) FILTER (WHERE status IN ($$failed$$,$$cancelled$$)) AS lost_orders,
  SUM(total) FILTER (WHERE status IN ($$failed$$,$$cancelled$$)) AS lost_total
FROM orders
GROUP BY 1
ORDER BY day DESC;


-- Auto-generated from joins-postgres.yaml (map@85230ed)
-- engine: postgres
-- view:   orders_funnel

-- Global funnel of orders
CREATE OR REPLACE VIEW vw_orders_funnel AS
SELECT
  COUNT(*) AS orders_total,
  COUNT(*) FILTER (WHERE status = $$pending$$)   AS pending,
  COUNT(*) FILTER (WHERE status = $$paid$$)      AS paid,
  COUNT(*) FILTER (WHERE status = $$completed$$) AS completed,
  COUNT(*) FILTER (WHERE status = $$failed$$)    AS failed,
  COUNT(*) FILTER (WHERE status = $$cancelled$$) AS cancelled,
  COUNT(*) FILTER (WHERE status = $$refunded$$)  AS refunded,
  ROUND(
    100.0 * COUNT(*) FILTER (WHERE status IN ($$paid$$,$$completed$$)) / GREATEST(COUNT(*),1),
    2
  ) AS payment_conversion_pct
FROM orders;

