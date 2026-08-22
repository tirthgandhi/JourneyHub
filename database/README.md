# JourneyHub Database Documentation

## Overview

**Database Name:** `journeyhub`

**Purpose:** Single source of truth for all travel planning data in JourneyHub. MySQL is used for all data storage - no hardcoded city lists, no JSON files, no static arrays anywhere in the application.

**Currency:** All monetary values (activity costs, expenses) are stored in **Indian Rupees (INR/₹)**.

**Technology:** MySQL/MariaDB with InnoDB engine, utf8mb4 charset for international character support.

---

## Database Schema

The JourneyHub database consists of **7 tables** that work together to provide complete travel planning functionality:

### 1. **users**
User accounts and authentication data.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `name` | VARCHAR(100) | User's full name |
| `email` | VARCHAR(150) | Unique login identifier |
| `password` | VARCHAR(255) | Hashed with `password_hash()` |
| `profile_photo` | VARCHAR(255) | Filename in `/assets/images/profiles/` |
| `language` | VARCHAR(10) | UI language preference (default: 'en') |
| `role` | ENUM('user','admin') | Access level |
| `created_at` | TIMESTAMP | Account creation date |
| `updated_at` | TIMESTAMP | Last profile update |

**Indexes:** email (unique), role

---

### 2. **trips**
Travel itineraries created by users.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `user_id` | INT UNSIGNED | Foreign key → users.id |
| `name` | VARCHAR(150) | Trip title |
| `description` | TEXT | Optional trip details |
| `start_date` | DATE | Trip start date |
| `end_date` | DATE | Trip end date |
| `cover_image` | VARCHAR(255) | Optional trip cover photo |
| `is_public` | BOOLEAN | Whether trip is publicly visible |
| `share_token` | VARCHAR(100) | Unique token for sharing (nullable, unique) |
| `created_at` | TIMESTAMP | Trip creation date |
| `updated_at` | TIMESTAMP | Last modification date |

**Foreign Keys:** 
- `user_id` → users.id (CASCADE on delete/update)

**Indexes:** user_id, start_date, is_public, share_token (unique)

---

### 3. **cities**
Master list of all destinations (searchable).

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `city_name` | VARCHAR(100) | City name |
| `state_name` | VARCHAR(100) | State/province/region name |
| `country_name` | VARCHAR(100) | Country name |
| `cost_index` | DECIMAL(5,2) | Relative cost indicator (0-100) |
| `popularity` | INT | Search ranking weight |
| `image` | VARCHAR(255) | Optional city image filename |
| `created_at` | TIMESTAMP | Record creation date |

**Indexes:** city_name, state_name, country_name, popularity, (city_name + country_name composite)

**Important:** Never hardcode city lists in PHP/JS. Always query this table for city search functionality.

---

### 4. **trip_stops**
Individual city stops within a trip itinerary.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `trip_id` | INT UNSIGNED | Foreign key → trips.id |
| `city_id` | INT UNSIGNED | Foreign key → cities.id |
| `start_date` | DATE | Stop arrival date |
| `end_date` | DATE | Stop departure date |
| `stop_order` | INT UNSIGNED | Sequence order (1, 2, 3, etc.) |
| `created_at` | TIMESTAMP | Record creation date |

**Foreign Keys:** 
- `trip_id` → trips.id (CASCADE on delete/update)
- `city_id` → cities.id (RESTRICT on delete, CASCADE on update)

**Indexes:** trip_id, city_id, stop_order

---

### 5. **activities**
Things to do in each city (sightseeing, food, adventures, etc.).

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `city_id` | INT UNSIGNED | Foreign key → cities.id |
| `name` | VARCHAR(150) | Activity name |
| `type` | VARCHAR(50) | Activity category (see types below) |
| `description` | TEXT | Detailed description |
| `cost` | DECIMAL(10,2) | Estimated cost in INR (₹) (must be >= 0) |
| `duration` | VARCHAR(50) | Time required (e.g., "2 hours", "Half day") |
| `image` | VARCHAR(255) | Optional activity image |
| `created_at` | TIMESTAMP | Record creation date |

**Activity Types:** sightseeing, food, adventure, culture, shopping, entertainment, nature

**Foreign Keys:** 
- `city_id` → cities.id (RESTRICT on delete, CASCADE on update)

**Indexes:** city_id, type, cost

**Constraints:** cost >= 0

---

### 6. **trip_activities**
Activities scheduled in trip stops (references activities table, no duplication).

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `trip_stop_id` | INT UNSIGNED | Foreign key → trip_stops.id |
| `activity_id` | INT UNSIGNED | Foreign key → activities.id |
| `activity_date` | DATE | Scheduled date |
| `activity_time` | TIME | Scheduled time (nullable) |
| `created_at` | TIMESTAMP | Record creation date |

**Foreign Keys:** 
- `trip_stop_id` → trip_stops.id (CASCADE on delete/update)
- `activity_id` → activities.id (RESTRICT on delete, CASCADE on update)

**Indexes:** trip_stop_id, activity_id, activity_date

**Important:** This table only references activities - never duplicates activity data.

---

### 7. **expenses**
Budget tracking for trips.

| Column | Type | Description |
|--------|------|-------------|
| `id` | INT UNSIGNED | Primary key, auto-increment |
| `trip_id` | INT UNSIGNED | Foreign key → trips.id |
| `category` | VARCHAR(50) | Expense category (see categories below) |
| `amount` | DECIMAL(10,2) | Cost in INR (₹) (must be >= 0) |
| `description` | VARCHAR(255) | Optional expense description |
| `created_at` | TIMESTAMP | Record creation date |

**Expense Categories:** transport, stay, activities, meals, other

**Foreign Keys:** 
- `trip_id` → trips.id (CASCADE on delete/update)

**Indexes:** trip_id, category

**Constraints:** amount >= 0

---

## Entity Relationships

```
users (1) ──────→ (many) trips
                     │
                     ├──→ (many) trip_stops
                     │              │
                     └──→ (many) expenses
                     
cities (1) ──────→ (many) trip_stops
    │
    └─────────→ (many) activities
                          │
trip_stops (1) ──→ (many) trip_activities ←── (many) activities
```

**Cascade Rules:**
- **CASCADE DELETE:** When a trip is deleted, all associated trip_stops, trip_activities, and expenses are automatically deleted
- **RESTRICT DELETE:** Cities and activities cannot be deleted if they are referenced by trip_stops or trip_activities

---

## Setup Instructions

### Prerequisites
- XAMPP installed and running
- Apache and MySQL services started in XAMPP Control Panel
- phpMyAdmin accessible at `http://localhost/phpmyadmin`

### Step-by-Step Database Creation

#### Step 1: Open phpMyAdmin
- Navigate to `http://localhost/phpmyadmin` in your browser
- You should see the phpMyAdmin interface

#### Step 2: Import Schema
1. Click the **"Import"** tab at the top
2. Click **"Choose File"** button
3. Navigate to `JourneyHub/database/` and select **`schema.sql`**
4. Scroll down and click **"Go"** button
5. You should see a success message: "Import has been successfully finished"
6. The database `journeyhub` will be created automatically

#### Step 3: Verify Database Creation
1. Refresh the left sidebar (click the refresh icon or refresh your browser)
2. You should now see **`journeyhub`** in the database list
3. Click on `journeyhub` to select it

#### Step 4: Import Seed Data
1. With `journeyhub` database selected, click the **"Import"** tab again
2. Click **"Choose File"** button
3. Select **`seed.sql`** from the same directory
4. Click **"Go"** button
5. You should see another success message

#### Step 5: Verify Data Import
1. In the left sidebar, you should see 7 tables:
   - activities
   - cities
   - expenses
   - trip_activities
   - trip_stops
   - trips
   - users
2. Click on any table name to browse its contents
3. Verify that data is present (e.g., users table should have 51 rows)

---

## Configuration

### Database Connection Settings

The database connection is configured in `config/db.php`.

**Default XAMPP settings** (usually no changes needed):
```php
DB_HOST: 'localhost'
DB_NAME: 'journeyhub'
DB_USER: 'root'
DB_PASS: '' // Empty password
```

**If you need to change these:**
1. Open `config/db.php` in a text editor
2. Update the `define()` constants at the top of the file
3. Save the file

**For production deployment:**
- Use strong database credentials
- Remove or comment out the `die()` error display
- Implement proper error logging instead

---

## Verification

### Running the Test Script

After importing both SQL files, verify everything is correct:

**Option 1: Command Line**
```bash
cd C:\xampp\htdocs\JourneyHub
php database/test.php
```

**Option 2: Browser**
Navigate to: `http://localhost/JourneyHub/database/test.php`

**Expected Output:**
```
✓ Test 1: Database connection - PASS
✓ Test 2: All 7 tables exist - PASS
✓ Test 3: 50 normal users exist - PASS
✓ Test 4: Admin user exists - PASS
✓ Test 5: Cities count (75) >= 70 - PASS
✓ Test 6: Activities exist (61) - PASS
✓ Bonus: Gujarat cities (state_name=Gujarat) - PASS

✓ All tests passed! Database is ready for use.
```

---

## Dev Test Credentials

**⚠️ WARNING: FOR LOCAL DEVELOPMENT ONLY - NEVER USE IN PRODUCTION**

### Admin Account
- **Email:** `admin@journeyhub.test`
- **Password:** `password123`
- **Role:** admin

### Test User Accounts
- **Email Pattern:** `user01@journeyhub.test` through `user50@journeyhub.test`
- **Password (all users):** `password123`
- **Role:** user

**Examples:**
- user01@journeyhub.test / password123
- user15@journeyhub.test / password123
- user42@journeyhub.test / password123

**Security Note:** These credentials use a consistent, pre-computed password hash for development convenience. Always change passwords and use unique salts in any deployed environment.

---

## Seed Data Summary

The seed.sql file populates the database with comprehensive test data:

| Table | Count | Description |
|-------|-------|-------------|
| **users** | 51 | 1 admin + 50 regular test users |
| **cities** | 75 | 30 Gujarat + 25 India + 20 Foreign |
| **activities** | 61 | Distributed across 15 major cities |
| **trips** | 10 | Sample trips with various destinations |
| **trip_stops** | 22 | Multiple stops per trip |
| **trip_activities** | 42 | Scheduled activities in trips |
| **expenses** | 50 | 5 expenses per trip (all categories) |

### City Distribution

**Gujarat Cities (30):** Ahmedabad, Surat, Vadodara, Rajkot, Bhavnagar, Jamnagar, Junagadh, Gandhinagar, Anand, Nadiad, Morbi, Mehsana, Bharuch, Navsari, Porbandar, Godhra, Veraval, Patan, Palanpur, Valsad, Vapi, Gandhidham, Amreli, Botad, Surendranagar, Dahod, Himatnagar, Kalol, Bhuj, Dwarka

**Other Indian Cities (25):** Mumbai, Pune, Nagpur, Delhi, Bengaluru, Hyderabad, Chennai, Kolkata, Jaipur, Udaipur, Jodhpur, Lucknow, Agra, Varanasi, Chandigarh, Amritsar, Goa, Kochi, Indore, Bhopal, Shimla, Darjeeling, Manali, Mysuru, Coimbatore

**Foreign Cities (20):** Dubai, Singapore, Paris, London, Rome, Tokyo, New York City, Toronto, Bangkok, Denpasar, Sydney, Amsterdam, Barcelona, Istanbul, Kuala Lumpur, Zurich, Hong Kong, Los Angeles, Berlin, Vienna

### Sample Trips Included

1. Gujarat Heritage Circuit (Ahmedabad → Vadodara → Bhuj → Dwarka)
2. South India Temple Tour (Chennai → Bengaluru → Mysuru)
3. Rajasthan Royal Experience (Jaipur → Udaipur → Jodhpur)
4. European Dream Vacation (Paris → London → Rome)
5. Southeast Asia Backpacking (Bangkok → Singapore → Bali)
6. Mumbai Weekend Getaway
7. Dubai Shopping Festival
8. Japan Cherry Blossom Tour (Tokyo)
9. North India Golden Triangle (Delhi → Agra → Jaipur)
10. Goa Beach Holiday

---

## Important Notes

### Data Integrity
- All foreign keys are properly constrained with CASCADE or RESTRICT rules
- No orphaned records exist in the seed data
- All dates are logically consistent (trip dates contain stop dates, stop dates contain activity dates)
- All monetary amounts are positive (CHECK constraints enforced)

### Query Guidelines
- **Always use prepared statements** when querying the database (security)
- **Never hardcode city data** in PHP or JavaScript - query the cities table
- **Check foreign key constraints** before attempting deletes
- **Use transactions** for operations that modify multiple related tables

### Performance Considerations
- Indexes are created on frequently queried columns (city names, user IDs, etc.)
- Composite index on (city_name, country_name) for faster city searches
- Consider adding more indexes as query patterns emerge in production

### Extending the Database
When adding new cities or activities:
- Ensure cost_index and popularity values are realistic
- Maintain consistency in state_name and country_name spelling
- Add activities only after their city exists
- Keep activity types within the defined categories

---

## Troubleshooting

### "Database connection failed"
- **Solution:** Ensure MySQL is running in XAMPP Control Panel
- Check that database `journeyhub` exists in phpMyAdmin
- Verify credentials in `config/db.php` match your MySQL setup

### "Table doesn't exist"
- **Solution:** Import `schema.sql` first, then `seed.sql`
- Make sure you selected the correct files during import
- Check for error messages during import in phpMyAdmin

### "Foreign key constraint fails"
- **Solution:** This usually means seed.sql was imported before schema.sql
- Drop the database and start over:
  1. In phpMyAdmin, select `journeyhub`
  2. Click "Drop" tab
  3. Confirm deletion
  4. Re-import schema.sql, then seed.sql

### Test script shows failures
- Run each test query manually in phpMyAdmin SQL tab to diagnose
- Verify row counts: `SELECT COUNT(*) FROM users;` should return 51
- Check for duplicate emails: `SELECT email, COUNT(*) FROM users GROUP BY email HAVING COUNT(*) > 1;`

### Import timeout on large seed.sql
- Increase max_execution_time in php.ini (default: 300 seconds)
- Or import via MySQL CLI: `mysql -u root journeyhub < database/seed.sql`

---

## Next Steps

With the database foundation complete, you're ready to build features:

1. **City Search API** - Query the `cities` table with filters
2. **Trip Planning** - Create/edit trips and add stops
3. **Activity Browser** - Display activities by city
4. **Budget Tracking** - Manage expenses per trip
5. **Sharing** - Use share_token for public trip links
6. **Admin Panel** - Manage cities, activities, and users

All data is already in place - just build the interfaces and APIs to interact with it!

---

## File Structure

```
JourneyHub/
├── config/
│   └── db.php              # PDO connection configuration
├── database/
│   ├── schema.sql          # Database structure (run first)
│   ├── seed.sql            # Test data (run second)
│   ├── test.php            # Validation script
│   └── README.md           # This file
```

---

**Database setup complete! 🚀**

For questions or issues, refer to the troubleshooting section above or check the test.php output for specific error messages.
