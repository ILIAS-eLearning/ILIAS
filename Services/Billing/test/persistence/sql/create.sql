CREATE TABLE IF NOT EXISTS seq (
  id INTEGER,
  tablename TEXT
);

CREATE TABLE IF NOT EXISTS bill (
  bill_pk INTEGER PRIMARY KEY,
  bill_number TEXT UNIQUE,
  bill_recipient_name TEXT,
  bill_recipient_street TEXT,
  bill_recipient_hnr TEXT,
  bill_recipient_zip TEXT,
  bill_recipient_city TEXT,
  bill_recipient_cntry TEXT,
  bill_date INTEGER,
  bill_title TEXT,
  bill_description TEXT,
  bill_vat REAL,
  bill_cost_center TEXT,
  bill_currency TEXT,
  bill_usr_id INTEGER,
  bill_year INTEGER,
  bill_final INTEGER,
  bill_context_id INTEGER
);


CREATE TABLE IF NOT EXISTS billitem (
  billitem_pk INTEGER PRIMARY KEY,
  bill_fk INTEGER,
  billitem_title TEXT,
  billitem_description TEXT,
  billitem_pta REAL,
  billitem_vat REAL,
  billitem_currency TEXT,
  billitem_context_id INTEGER,
  billitem_final INTEGER,
  FOREIGN KEY(bill_fk) REFERENCES bill(bill_pk)
);

CREATE TABLE IF NOT EXISTS coupon (
  coupon_pk INTEGER PRIMARY KEY,
  coupon_code TEXT,
  coupon_value REAL,
  coupon_last_change INTEGER,
  coupon_expires INTEGER,
  coupon_usr_id INTEGER,
  coupon_active INTEGER,
  coupon_created INTEGER
);
