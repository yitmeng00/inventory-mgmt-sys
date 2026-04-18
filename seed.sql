-- Seed Data — run AFTER schema.sql

USE `inventory_db`;

INSERT INTO `user` (`staff_id`, `username`, `password`, `first_name`, `last_name`, `email`, `designation`, `role`, `status`) VALUES
('STF001', 'alice.tan',   '$2y$10$TKh8H1.PfuA2iNDh0MFzjuPHC2kNZJjd.QKYTVPcWIK2PFyYsxJWi', 'Alice',   'Tan',     'alice.tan@company.com',   'Warehouse Staff',    'staff', 'active'),
('STF002', 'bob.lim',     '$2y$10$TKh8H1.PfuA2iNDh0MFzjuPHC2kNZJjd.QKYTVPcWIK2PFyYsxJWi', 'Bob',     'Lim',     'bob.lim@company.com',     'Inventory Clerk',    'staff', 'active'),
('STF003', 'carol.ng',    '$2y$10$TKh8H1.PfuA2iNDh0MFzjuPHC2kNZJjd.QKYTVPcWIK2PFyYsxJWi', 'Carol',   'Ng',      'carol.ng@company.com',    'Procurement Officer','staff', 'active'),
('STF004', 'david.wong',  '$2y$10$TKh8H1.PfuA2iNDh0MFzjuPHC2kNZJjd.QKYTVPcWIK2PFyYsxJWi', 'David',   'Wong',    'david.wong@company.com',  'Sales Executive',    'staff', 'active'),
('MGR001', 'mary.chen',   '$2y$10$TKh8H1.PfuA2iNDh0MFzjuPHC2kNZJjd.QKYTVPcWIK2PFyYsxJWi', 'Mary',    'Chen',    'mary.chen@company.com',   'Operations Manager', 'admin', 'active');

INSERT INTO `category` (`category_name`) VALUES
('Electronics'),
('Office Supplies'),
('Furniture'),
('Networking & IT'),
('Tools & Equipment'),
('Stationery'),
('Safety & Workwear');

INSERT INTO `supplier` (`supplier_code`, `supplier_name`, `contact_person`, `contact_no`, `email`, `location`) VALUES
('SUP001', 'TechCore Distribution',   'James Holt',    '+60-12-345-6789', 'james@techcore.com',    'Kuala Lumpur, MY'),
('SUP002', 'OfficePro Supplies',      'Sarah Yee',     '+60-11-234-5678', 'sarah@officepro.com',   'Petaling Jaya, MY'),
('SUP003', 'FurniMax Sdn Bhd',        'Raj Kumar',     '+60-17-890-1234', 'raj@furnimax.com',      'Shah Alam, MY'),
('SUP004', 'NetLink Technologies',    'Cindy Loh',     '+60-16-567-8901', 'cindy@netlink.com',     'Penang, MY'),
('SUP005', 'SafeGuard Equipment',     'Hassan Ismail', '+60-19-678-9012', 'hassan@safeguard.com',  'Johor Bahru, MY');

INSERT INTO `product` (`product_code`, `product_name`, `description`, `category_id`, `supplier_id`, `cost_price`, `sale_price`, `quantity`) VALUES
-- Electronics (category 1, supplier 1)
('PRD001', 'Dell 27" Monitor',         '4K UHD IPS display, USB-C',         1, 1,  750.00,  999.00,  45),
('PRD002', 'Logitech MX Keys Keyboard','Wireless, multi-device pairing',     1, 1,   95.00,  149.00,  80),
('PRD003', 'Sony WH-1000XM5 Headset', 'Noise-cancelling, Bluetooth 5.2',    1, 1,  220.00,  349.00,  12),
('PRD004', 'Webcam HD 1080p',          'Auto-focus, built-in noise filter',  1, 1,   55.00,   89.00,   8),
('PRD005', 'USB-C Docking Station',    '12-in-1, 100W PD charging',          1, 1,  130.00,  199.00,  35),

-- Office Supplies (category 2, supplier 2)
('PRD006', 'A4 Copy Paper (Box/5 Ream)','80gsm premium white',               2, 2,   22.00,   38.00, 200),
('PRD007', 'Ballpoint Pen (Box/50)',   'Blue ink, smooth write',             2, 2,    8.50,   15.00, 150),
('PRD008', 'Stapler Heavy Duty',       'Up to 50 sheets',                    2, 2,   18.00,   29.00,  60),
('PRD009', 'Whiteboard Marker Set',    '4 colors, chisel tip',               2, 2,    7.00,   12.00,  15),
('PRD010', 'File Cabinet Organizer',   'A4, 3-tier mesh steel',              2, 2,   32.00,   55.00,  40),

-- Furniture (category 3, supplier 3)
('PRD011', 'Ergonomic Office Chair',  'Lumbar support, adjustable armrest',  3, 3,  320.00,  549.00,  18),
('PRD012', 'Standing Desk 140cm',     'Electric height adjustable',          3, 3,  650.00,  999.00,   7),
('PRD013', 'Meeting Table 180cm',     '6-seater, laminate top',              3, 3,  420.00,  699.00,   5),
('PRD014', 'Visitor Chair',           'Stackable, fabric seat',              3, 3,   85.00,  139.00,  30),

-- Networking (category 4, supplier 4)
('PRD015', 'TP-Link 8-Port Switch',   'Gigabit unmanaged',                   4, 4,   65.00,   99.00,  25),
('PRD016', 'CAT6 Patch Cable 2m',     'RJ45, UTP, 10-pack',                  4, 4,   12.00,   22.00,  90),
('PRD017', 'Wi-Fi 6 Access Point',    'AX3000 dual-band, PoE',               4, 4,  180.00,  279.00,  14),
('PRD018', 'UPS 650VA',               'Line-interactive, 4 outlets',         4, 4,  145.00,  229.00,   0),

-- Tools (category 5, supplier 5)
('PRD019', 'Cordless Drill Set',      '18V, 2 batteries, case',              5, 5,  145.00,  229.00,  22),
('PRD020', 'Label Printer',           'Thermal, USB + Bluetooth',            5, 5,   88.00,  139.00,  17);

-- ------------------------------------------------------------
-- Transactions — spread across Jan–Apr 2026
-- type_id 1 = Sale, type_id 2 = Purchase
-- NOTE: quantity here is for the transaction record; stock in
--       product table above already reflects net position.
-- ------------------------------------------------------------
INSERT INTO `transaction` (`transaction_code`, `product_id`, `type_id`, `quantity`, `unit_price`, `created`) VALUES

-- January 2026 — Purchases (restocking)
('T000001',  6, 2, 300,  22.00, '2026-01-03 09:00:00'),
('T000002',  7, 2, 200,   8.50, '2026-01-03 09:15:00'),
('T000003',  1, 2,  20, 750.00, '2026-01-05 10:00:00'),
('T000004',  2, 2,  50,  95.00, '2026-01-05 10:30:00'),
('T000005', 11, 2,  15, 320.00, '2026-01-07 14:00:00'),
('T000006', 14, 2,  25,  85.00, '2026-01-07 14:30:00'),
('T000007', 15, 2,  20,  65.00, '2026-01-08 11:00:00'),
('T000008', 16, 2, 100,  12.00, '2026-01-08 11:15:00'),

-- January 2026 — Sales
('T000009',  1, 1,  5, 999.00, '2026-01-10 10:00:00'),
('T000010',  2, 1, 10, 149.00, '2026-01-12 11:00:00'),
('T000011',  6, 1, 40,  38.00, '2026-01-14 09:00:00'),
('T000012',  7, 1, 30,  15.00, '2026-01-14 09:30:00'),
('T000013', 11, 1,  3, 549.00, '2026-01-16 14:00:00'),
('T000014', 14, 1,  5, 139.00, '2026-01-18 10:00:00'),
('T000015',  5, 1,  8, 199.00, '2026-01-20 15:00:00'),
('T000016', 15, 1,  5,  99.00, '2026-01-22 11:00:00'),

-- February 2026 — Purchases
('T000017',  3, 2,  15, 220.00, '2026-02-03 09:00:00'),
('T000018',  4, 2,  20,  55.00, '2026-02-03 09:30:00'),
('T000019', 12, 2,   8, 650.00, '2026-02-05 10:00:00'),
('T000020', 17, 2,  10, 180.00, '2026-02-06 11:00:00'),
('T000021', 19, 2,  15, 145.00, '2026-02-07 09:00:00'),
('T000022',  8, 2,  40,  18.00, '2026-02-10 10:00:00'),

-- February 2026 — Sales
('T000023',  1, 1,  6, 999.00, '2026-02-06 10:00:00'),
('T000024',  3, 1,  4, 349.00, '2026-02-08 11:00:00'),
('T000025',  2, 1, 12, 149.00, '2026-02-10 09:00:00'),
('T000026',  6, 1, 35,  38.00, '2026-02-12 09:30:00'),
('T000027', 11, 1,  4, 549.00, '2026-02-14 14:00:00'),
('T000028', 12, 1,  2, 999.00, '2026-02-17 10:00:00'),
('T000029', 17, 1,  3, 279.00, '2026-02-19 11:00:00'),
('T000030',  5, 1,  6, 199.00, '2026-02-21 15:00:00'),

-- March 2026 — Purchases
('T000031',  6, 2, 250,  22.00, '2026-03-02 09:00:00'),
('T000032',  1, 2,  25, 750.00, '2026-03-03 10:00:00'),
('T000033', 13, 2,   5, 420.00, '2026-03-04 11:00:00'),
('T000034', 20, 2,  20,  88.00, '2026-03-05 09:00:00'),
('T000035',  9, 2,  30,   7.00, '2026-03-05 09:30:00'),
('T000036', 18, 2,   5, 145.00, '2026-03-07 14:00:00'),

-- March 2026 — Sales
('T000037',  1, 1,  8, 999.00, '2026-03-05 10:00:00'),
('T000038',  2, 1, 15, 149.00, '2026-03-07 11:00:00'),
('T000039',  6, 1, 50,  38.00, '2026-03-09 09:00:00'),
('T000040', 11, 1,  5, 549.00, '2026-03-11 14:00:00'),
('T000041', 12, 1,  3, 999.00, '2026-03-13 10:00:00'),
('T000042',  3, 1,  3, 349.00, '2026-03-15 11:00:00'),
('T000043', 19, 1,  4, 229.00, '2026-03-17 09:00:00'),
('T000044',  5, 1, 10, 199.00, '2026-03-19 15:00:00'),
('T000045', 20, 1,  5, 139.00, '2026-03-21 10:00:00'),
('T000046', 15, 1,  6,  99.00, '2026-03-24 11:00:00'),

-- April 2026 — Purchases
('T000047',  2, 2,  60,  95.00, '2026-04-01 09:00:00'),
('T000048',  4, 2,  15,  55.00, '2026-04-02 10:00:00'),
('T000049', 10, 2,  30,  32.00, '2026-04-03 09:30:00'),
('T000050', 16, 2, 100,  12.00, '2026-04-04 11:00:00'),

-- April 2026 — Sales
('T000051',  1, 1,  7, 999.00, '2026-04-03 10:00:00'),
('T000052',  2, 1, 18, 149.00, '2026-04-05 11:00:00'),
('T000053',  6, 1, 45,  38.00, '2026-04-07 09:00:00'),
('T000054', 11, 1,  3, 549.00, '2026-04-09 14:00:00'),
('T000055',  5, 1,  7, 199.00, '2026-04-11 15:00:00'),
('T000056',  3, 1,  2, 349.00, '2026-04-13 11:00:00'),
('T000057', 19, 1,  3, 229.00, '2026-04-15 09:00:00'),
('T000058', 20, 1,  4, 139.00, '2026-04-16 10:00:00');
