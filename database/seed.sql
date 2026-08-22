-- ====================================================================
-- JourneyHub Seed Data
-- Branch: feature/database
-- ====================================================================
-- This script populates the database with test data
-- Run AFTER schema.sql has been executed
-- Dev Password for ALL users: "password123"
-- 
-- IMPORTANT: All monetary values (activity costs, expenses) are in INR (₹)
-- ====================================================================

USE journeyhub;

-- ====================================================================
-- USERS: 50 normal users + 1 admin (51 total)
-- Password for ALL accounts: "password123"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi
-- ====================================================================

INSERT INTO users (name, email, password, role) VALUES
-- Admin user
('Vikram Sharma', 'admin@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),

-- Normal users (50 total)
('Rahul Patel', 'rahul.patel@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Priya Shah', 'priya.shah@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Arjun Mehta', 'arjun.mehta@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Neha Desai', 'neha.desai@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Karan Singh', 'karan.singh@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Anjali Verma', 'anjali.verma@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Rohan Kumar', 'rohan.kumar@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Sneha Joshi', 'sneha.joshi@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Aditya Gupta', 'aditya.gupta@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Divya Reddy', 'divya.reddy@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Siddharth Iyer', 'siddharth.iyer@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Kavya Nair', 'kavya.nair@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Aman Malhotra', 'aman.malhotra@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Riya Kapoor', 'riya.kapoor@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Vivek Pandey', 'vivek.pandey@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Pooja Agarwal', 'pooja.agarwal@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Nikhil Rao', 'nikhil.rao@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Ishita Bansal', 'ishita.bansal@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Varun Thakur', 'varun.thakur@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Ananya Chopra', 'ananya.chopra@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Ayush Mishra', 'ayush.mishra@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Tanvi Saxena', 'tanvi.saxena@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Kunal Shukla', 'kunal.shukla@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Meera Srinivasan', 'meera.srinivasan@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Dev Bhatt', 'dev.bhatt@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Shreya Modi', 'shreya.modi@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Harsh Trivedi', 'harsh.trivedi@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Sakshi Jain', 'sakshi.jain@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Aryan Bose', 'aryan.bose@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Diya Kulkarni', 'diya.kulkarni@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Vishal Pillai', 'vishal.pillai@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Nidhi Ghosh', 'nidhi.ghosh@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Samarth Deshpande', 'samarth.deshpande@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Tara Menon', 'tara.menon@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Shubham Yadav', 'shubham.yadav@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Aarti Chauhan', 'aarti.chauhan@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Mayank Dubey', 'mayank.dubey@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Simran Kaur', 'simran.kaur@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Pranav Shetty', 'pranav.shetty@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Ritu Bajaj', 'ritu.bajaj@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Yash Ahuja', 'yash.ahuja@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Nisha Tiwari', 'nisha.tiwari@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Akash Bhardwaj', 'akash.bhardwaj@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Kritika Arora', 'kritika.arora@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Dhruv Soni', 'dhruv.soni@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Pallavi Sethi', 'pallavi.sethi@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Gaurav Naik', 'gaurav.naik@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Aditi Raghavan', 'aditi.raghavan@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Manish Kohli', 'manish.kohli@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user'),
('Radhika Goswami', 'radhika.goswami@journeyhub.test', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user');

-- ====================================================================
-- CITIES: 75 total cities
-- GROUP 1: All major cities of Gujarat, India (30 cities)
-- GROUP 2: Major cities across other Indian states (25 cities)
-- GROUP 3: Major foreign cities (20 cities)
-- ====================================================================

INSERT INTO cities (city_name, state_name, country_name, cost_index, popularity) VALUES
-- GROUP 1: Gujarat Cities (30 cities) - ALL state_name='Gujarat', country_name='India'
-- cost_index = Approximate daily travel cost per person in INR (₹)
('Ahmedabad', 'Gujarat', 'India', 3500.00, 95),
('Surat', 'Gujarat', 'India', 3200.00, 88),
('Vadodara', 'Gujarat', 'India', 3400.00, 85),
('Rajkot', 'Gujarat', 'India', 2800.00, 78),
('Bhavnagar', 'Gujarat', 'India', 2500.00, 65),
('Jamnagar', 'Gujarat', 'India', 2700.00, 70),
('Junagadh', 'Gujarat', 'India', 2400.00, 60),
('Gandhinagar', 'Gujarat', 'India', 3600.00, 82),
('Anand', 'Gujarat', 'India', 2200.00, 55),
('Nadiad', 'Gujarat', 'India', 2000.00, 48),
('Morbi', 'Gujarat', 'India', 2300.00, 52),
('Mehsana', 'Gujarat', 'India', 2200.00, 50),
('Bharuch', 'Gujarat', 'India', 2400.00, 58),
('Navsari', 'Gujarat', 'India', 2300.00, 54),
('Porbandar', 'Gujarat', 'India', 2600.00, 62),
('Godhra', 'Gujarat', 'India', 1900.00, 45),
('Veraval', 'Gujarat', 'India', 2400.00, 56),
('Patan', 'Gujarat', 'India', 2000.00, 47),
('Palanpur', 'Gujarat', 'India', 2100.00, 49),
('Valsad', 'Gujarat', 'India', 2300.00, 53),
('Vapi', 'Gujarat', 'India', 2600.00, 60),
('Gandhidham', 'Gujarat', 'India', 2400.00, 57),
('Amreli', 'Gujarat', 'India', 2000.00, 46),
('Botad', 'Gujarat', 'India', 1800.00, 40),
('Surendranagar', 'Gujarat', 'India', 2100.00, 51),
('Dahod', 'Gujarat', 'India', 1800.00, 42),
('Himatnagar', 'Gujarat', 'India', 2000.00, 48),
('Kalol', 'Gujarat', 'India', 2100.00, 50),
('Bhuj', 'Gujarat', 'India', 2700.00, 68),
('Dwarka', 'Gujarat', 'India', 2900.00, 75),

-- GROUP 2: Major Indian Cities (25 cities) - country_name='India'
-- cost_index = Approximate daily travel cost per person in INR (₹)
('Mumbai', 'Maharashtra', 'India', 6500.00, 98),
('Pune', 'Maharashtra', 'India', 4500.00, 92),
('Nagpur', 'Maharashtra', 'India', 3500.00, 75),
('Delhi', 'Delhi', 'India', 5500.00, 99),
('Bengaluru', 'Karnataka', 'India', 5800.00, 97),
('Hyderabad', 'Telangana', 'India', 5000.00, 94),
('Chennai', 'Tamil Nadu', 'India', 4800.00, 93),
('Kolkata', 'West Bengal', 'India', 4200.00, 91),
('Jaipur', 'Rajasthan', 'India', 4000.00, 89),
('Udaipur', 'Rajasthan', 'India', 4300.00, 87),
('Jodhpur', 'Rajasthan', 'India', 3800.00, 84),
('Lucknow', 'Uttar Pradesh', 'India', 3500.00, 78),
('Agra', 'Uttar Pradesh', 'India', 3800.00, 95),
('Varanasi', 'Uttar Pradesh', 'India', 3300.00, 88),
('Chandigarh', 'Chandigarh', 'India', 4500.00, 86),
('Amritsar', 'Punjab', 'India', 3500.00, 83),
('Goa', 'Goa', 'India', 5200.00, 96),
('Kochi', 'Kerala', 'India', 4300.00, 85),
('Indore', 'Madhya Pradesh', 'India', 3300.00, 76),
('Bhopal', 'Madhya Pradesh', 'India', 3200.00, 74),
('Shimla', 'Himachal Pradesh', 'India', 4200.00, 82),
('Darjeeling', 'West Bengal', 'India', 3800.00, 80),
('Manali', 'Himachal Pradesh', 'India', 4500.00, 85),
('Mysuru', 'Karnataka', 'India', 3500.00, 79),
('Coimbatore', 'Tamil Nadu', 'India', 3300.00, 72),

-- GROUP 3: Major Foreign Cities (20 cities)
-- cost_index = Approximate daily travel cost per person in INR (₹)
('Dubai', 'Dubai', 'UAE', 14000.00, 99),
('Singapore', 'Singapore', 'Singapore', 15000.00, 98),
('Paris', 'Île-de-France', 'France', 17000.00, 99),
('London', 'England', 'United Kingdom', 20000.00, 99),
('Rome', 'Lazio', 'Italy', 12000.00, 96),
('Tokyo', 'Tokyo', 'Japan', 16000.00, 97),
('New York City', 'New York', 'USA', 22000.00, 99),
('Toronto', 'Ontario', 'Canada', 13000.00, 92),
('Bangkok', 'Bangkok', 'Thailand', 4500.00, 94),
('Denpasar', 'Bali', 'Indonesia', 5000.00, 91),
('Sydney', 'New South Wales', 'Australia', 15000.00, 95),
('Amsterdam', 'North Holland', 'Netherlands', 14500.00, 93),
('Barcelona', 'Catalonia', 'Spain', 12000.00, 96),
('Istanbul', 'Istanbul', 'Turkey', 6000.00, 90),
('Kuala Lumpur', 'Federal Territory', 'Malaysia', 4800.00, 88),
('Zurich', 'Zurich', 'Switzerland', 25000.00, 91),
('Hong Kong', 'Hong Kong', 'China', 17000.00, 96),
('Los Angeles', 'California', 'USA', 18000.00, 94),
('Berlin', 'Berlin', 'Germany', 11000.00, 92),
('Vienna', 'Vienna', 'Austria', 12500.00, 89);

-- ====================================================================
-- ACTIVITIES: 61 activities for 15 major cities
-- All costs in Indian Rupees (INR/₹)
-- ====================================================================

-- Ahmedabad activities (city_id = 1)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(1, 'Sabarmati Ashram Visit', 'culture', 'Explore Mahatma Gandhi''s historic ashram on the banks of Sabarmati River', 0.00, '2 hours'),
(1, 'Adalaj Stepwell Tour', 'sightseeing', 'Visit the stunning 15th century stepwell with intricate carvings', 50.00, '1.5 hours'),
(1, 'Kankaria Lake Boating', 'entertainment', 'Enjoy boating and attractions at Ahmedabad''s largest lake', 100.00, '2 hours'),
(1, 'Law Garden Night Market', 'shopping', 'Shop for traditional handicrafts and Gujarati textiles', 1000.00, '2 hours'),
(1, 'Gujarati Thali Experience', 'food', 'Savor authentic unlimited Gujarati cuisine at a traditional restaurant', 400.00, '1.5 hours');

-- Surat activities (city_id = 2)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(2, 'Dumas Beach Evening', 'nature', 'Relax at the black sand beach with street food stalls', 200.00, '2 hours'),
(2, 'Textile Market Tour', 'shopping', 'Explore Surat''s famous fabric markets', 500.00, '3 hours'),
(2, 'Sarthana Nature Park', 'nature', 'Visit the zoo and botanical gardens', 100.00, '3 hours');

-- Vadodara activities (city_id = 3)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(3, 'Laxmi Vilas Palace', 'sightseeing', 'Tour the magnificent royal palace, four times the size of Buckingham Palace', 500.00, '2 hours'),
(3, 'Champaner-Pavagadh Trek', 'adventure', 'UNESCO World Heritage Site with historic fort and temple', 250.00, 'Full day'),
(3, 'Sayaji Garden Visit', 'nature', 'Explore one of the largest public gardens in Western India', 20.00, '2 hours');

-- Mumbai activities (city_id = 31)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(31, 'Gateway of India Visit', 'sightseeing', 'Visit Mumbai''s iconic monument overlooking the Arabian Sea', 0.00, '1 hour'),
(31, 'Marine Drive Evening Walk', 'entertainment', 'Stroll along the Queen''s Necklace with stunning sea views', 0.00, '1.5 hours'),
(31, 'Mumbai Street Food Tour', 'food', 'Experience Mumbai''s legendary street food scene', 800.00, '3 hours'),
(31, 'Elephanta Caves Ferry', 'culture', 'UNESCO World Heritage cave temples on Elephanta Island', 600.00, 'Half day');

-- Delhi activities (city_id = 34)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(34, 'Red Fort Exploration', 'sightseeing', 'Explore the massive 17th-century Mughal fort', 250.00, '2 hours'),
(34, 'India Gate & Rajpath', 'sightseeing', 'Visit the war memorial and stroll the ceremonial boulevard', 0.00, '1 hour'),
(34, 'Chandni Chowk Food Walk', 'food', 'Taste authentic Old Delhi street food in the historic bazaar', 800.00, '3 hours'),
(34, 'Qutub Minar Visit', 'culture', 'See the UNESCO World Heritage Site - tallest brick minaret', 200.00, '1.5 hours'),
(34, 'Lotus Temple', 'culture', 'Visit the stunning Baháʼí House of Worship', 0.00, '1 hour');

-- Bengaluru activities (city_id = 35)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(35, 'Lalbagh Botanical Garden', 'nature', 'Explore 240 acres of diverse flora and glasshouse', 50.00, '2 hours'),
(35, 'Bangalore Palace Tour', 'sightseeing', 'Visit the Tudor-style royal palace with beautiful architecture', 300.00, '1.5 hours'),
(35, 'Craft Beer Tasting', 'food', 'Sample India''s best craft beers at microbreweries', 1200.00, '2 hours'),
(35, 'Cubbon Park Cycling', 'nature', 'Cycle through the green heart of the city', 150.00, '2 hours');

-- Goa activities (city_id = 47)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(47, 'Baga Beach Water Sports', 'adventure', 'Parasailing, jet skiing, and banana boat rides', 3000.00, 'Half day'),
(47, 'Old Goa Churches Tour', 'culture', 'Visit UNESCO World Heritage Portuguese churches', 100.00, '3 hours'),
(47, 'Spice Plantation Tour', 'nature', 'Guided tour with traditional Goan lunch', 1200.00, 'Half day'),
(47, 'Sunset Cruise', 'entertainment', 'Evening cruise on Mandovi River with live music', 1800.00, '2 hours');

-- Jaipur activities (city_id = 39)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(39, 'Amber Fort Visit', 'sightseeing', 'Explore the majestic hilltop fort with elephant ride option', 750.00, '3 hours'),
(39, 'Hawa Mahal Photo Stop', 'sightseeing', 'Visit the iconic Palace of Winds', 200.00, '30 minutes'),
(39, 'City Palace Tour', 'culture', 'Tour the royal residence and museums', 500.00, '2 hours'),
(39, 'Johari Bazaar Shopping', 'shopping', 'Shop for jewelry, textiles, and handicrafts', 2000.00, '2 hours');

-- Dubai activities (city_id = 56)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(56, 'Burj Khalifa Observation Deck', 'sightseeing', 'Visit the world''s tallest building observation deck', 7000.00, '2 hours'),
(56, 'Desert Safari', 'adventure', 'Dune bashing, camel ride, and BBQ dinner in the desert', 10000.00, 'Half day'),
(56, 'Dubai Mall Shopping', 'shopping', 'Explore one of the world''s largest shopping malls', 5000.00, '4 hours'),
(56, 'Gold Souk Tour', 'shopping', 'Visit the traditional gold market', 500.00, '1.5 hours'),
(56, 'Dubai Fountain Show', 'entertainment', 'Watch the spectacular choreographed fountain display', 0.00, '30 minutes');

-- Singapore activities (city_id = 57)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(57, 'Gardens by the Bay', 'nature', 'Explore futuristic gardens with Supertrees and Cloud Forest', 2500.00, '3 hours'),
(57, 'Marina Bay Sands SkyPark', 'sightseeing', 'Rooftop observation deck with stunning city views', 2500.00, '1.5 hours'),
(57, 'Sentosa Island', 'entertainment', 'Beach resort with attractions and Universal Studios', 6000.00, 'Full day'),
(57, 'Hawker Centre Food Tour', 'food', 'Taste authentic Singaporean street food', 1500.00, '2 hours');

-- Paris activities (city_id = 58)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(58, 'Eiffel Tower Visit', 'sightseeing', 'Iconic iron tower with panoramic views of Paris', 3500.00, '2 hours'),
(58, 'Louvre Museum', 'culture', 'World''s largest art museum featuring Mona Lisa', 2500.00, '4 hours'),
(58, 'Seine River Cruise', 'entertainment', 'Romantic boat tour with views of monuments', 2000.00, '1 hour'),
(58, 'Montmartre Walking Tour', 'culture', 'Explore artistic hilltop neighborhood and Sacré-Cœur', 1500.00, '3 hours');

-- London activities (city_id = 59)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(59, 'Tower of London', 'sightseeing', 'Historic castle housing the Crown Jewels', 4000.00, '3 hours'),
(59, 'British Museum', 'culture', 'World-renowned museum of human history and culture', 0.00, '3 hours'),
(59, 'Thames River Cruise', 'entertainment', 'Scenic cruise past London''s iconic landmarks', 2500.00, '1 hour'),
(59, 'Buckingham Palace Tour', 'sightseeing', 'Royal residence and Changing of the Guard ceremony', 3500.00, '2 hours');

-- Tokyo activities (city_id = 61)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(61, 'Senso-ji Temple', 'culture', 'Tokyo''s oldest Buddhist temple in Asakusa', 0.00, '1.5 hours'),
(61, 'Shibuya Crossing Experience', 'entertainment', 'Visit the world''s busiest pedestrian crossing', 0.00, '30 minutes'),
(61, 'Tsukiji Fish Market', 'food', 'Fresh sushi breakfast at famous fish market', 3000.00, '2 hours'),
(61, 'Tokyo Skytree', 'sightseeing', 'World''s tallest tower with observation decks', 3500.00, '2 hours');

-- New York City activities (city_id = 62)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(62, 'Statue of Liberty & Ellis Island', 'sightseeing', 'Ferry tour to iconic monument and immigration museum', 4500.00, 'Half day'),
(62, 'Central Park Walk', 'nature', 'Stroll through Manhattan''s famous urban park', 0.00, '2 hours'),
(62, 'Empire State Building', 'sightseeing', 'Art Deco skyscraper observation deck', 5000.00, '2 hours'),
(62, 'Broadway Show', 'entertainment', 'World-class theater performance', 12000.00, '3 hours');

-- Bangkok activities (city_id = 64)
INSERT INTO activities (city_id, name, type, description, cost, duration) VALUES
(64, 'Grand Palace & Wat Phra Kaew', 'culture', 'Royal palace complex with Temple of the Emerald Buddha', 800.00, '3 hours'),
(64, 'Floating Market Tour', 'shopping', 'Traditional market on boats in canals', 1200.00, 'Half day'),
(64, 'Thai Massage Experience', 'entertainment', 'Authentic traditional Thai massage', 1500.00, '2 hours'),
(64, 'Street Food Night Tour', 'food', 'Explore Bangkok''s legendary street food scene', 1000.00, '3 hours');

-- ====================================================================
-- TRIPS: 10 sample trips
-- ====================================================================

INSERT INTO trips (user_id, name, description, start_date, end_date, is_public, share_token) VALUES
(2, 'Gujarat Heritage Circuit', 'Exploring the cultural heritage of Gujarat', '2024-12-15', '2024-12-22', TRUE, 'gh2024abc'),
(3, 'South India Temple Tour', 'Spiritual journey across Tamil Nadu and Karnataka', '2024-11-10', '2024-11-20', TRUE, 'sit2024xyz'),
(5, 'Rajasthan Royal Experience', 'Visiting the palaces and forts of Rajasthan', '2025-01-05', '2025-01-15', FALSE, NULL),
(7, 'European Dream Vacation', 'Paris, London, and Rome adventure', '2025-03-01', '2025-03-15', TRUE, 'edv2025abc'),
(10, 'Southeast Asia Backpacking', 'Budget travel through Thailand, Singapore, and Bali', '2024-12-01', '2024-12-20', TRUE, 'seab2024xyz'),
(12, 'Mumbai Weekend Getaway', 'Quick trip to the city of dreams', '2024-11-25', '2024-11-27', FALSE, NULL),
(15, 'Dubai Shopping Festival', 'Luxury shopping and desert adventures in Dubai', '2025-01-20', '2025-01-25', TRUE, 'dsf2025abc'),
(18, 'Japan Cherry Blossom Tour', 'Experiencing spring in Tokyo and beyond', '2025-04-01', '2025-04-10', TRUE, 'jcb2025xyz'),
(22, 'North India Golden Triangle', 'Delhi, Agra, and Jaipur classic tour', '2024-12-05', '2024-12-12', TRUE, 'nigt2024abc'),
(25, 'Goa Beach Holiday', 'Relaxing beach vacation with water sports', '2024-12-28', '2025-01-03', FALSE, NULL);

-- ====================================================================
-- TRIP_STOPS: 22 stops across 10 trips
-- ====================================================================

-- Trip 1: Gujarat Heritage Circuit (Ahmedabad -> Vadodara -> Bhuj -> Dwarka)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(1, 1, '2024-12-15', '2024-12-17', 1),  -- Ahmedabad
(1, 3, '2024-12-18', '2024-12-19', 2),  -- Vadodara
(1, 29, '2024-12-20', '2024-12-21', 3), -- Bhuj
(1, 30, '2024-12-22', '2024-12-22', 4); -- Dwarka

-- Trip 2: South India Temple Tour (Chennai -> Bengaluru -> Mysuru)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(2, 37, '2024-11-10', '2024-11-13', 1), -- Chennai
(2, 35, '2024-11-14', '2024-11-17', 2), -- Bengaluru
(2, 54, '2024-11-18', '2024-11-20', 3); -- Mysuru

-- Trip 3: Rajasthan Royal Experience (Jaipur -> Udaipur -> Jodhpur)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(3, 39, '2025-01-05', '2025-01-09', 1), -- Jaipur
(3, 40, '2025-01-10', '2025-01-12', 2), -- Udaipur
(3, 41, '2025-01-13', '2025-01-15', 3); -- Jodhpur

-- Trip 4: European Dream Vacation (Paris -> London -> Rome)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(4, 58, '2025-03-01', '2025-03-05', 1), -- Paris
(4, 59, '2025-03-06', '2025-03-10', 2), -- London
(4, 60, '2025-03-11', '2025-03-15', 3); -- Rome

-- Trip 5: Southeast Asia Backpacking (Bangkok -> Singapore -> Denpasar)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(5, 64, '2024-12-01', '2024-12-07', 1), -- Bangkok
(5, 57, '2024-12-08', '2024-12-13', 2), -- Singapore
(5, 65, '2024-12-14', '2024-12-20', 3); -- Denpasar (Bali)

-- Trip 6: Mumbai Weekend Getaway
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(6, 31, '2024-11-25', '2024-11-27', 1); -- Mumbai

-- Trip 7: Dubai Shopping Festival
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(7, 56, '2025-01-20', '2025-01-25', 1); -- Dubai

-- Trip 8: Japan Cherry Blossom Tour
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(8, 61, '2025-04-01', '2025-04-10', 1); -- Tokyo

-- Trip 9: North India Golden Triangle (Delhi -> Agra -> Jaipur)
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(9, 34, '2024-12-05', '2024-12-07', 1), -- Delhi
(9, 43, '2024-12-08', '2024-12-09', 2), -- Agra
(9, 39, '2024-12-10', '2024-12-12', 3); -- Jaipur

-- Trip 10: Goa Beach Holiday
INSERT INTO trip_stops (trip_id, city_id, start_date, end_date, stop_order) VALUES
(10, 47, '2024-12-28', '2025-01-03', 1); -- Goa

-- ====================================================================
-- TRIP_ACTIVITIES: 42 activities scheduled across trip stops
-- ====================================================================

-- Trip 1 Stop 1: Ahmedabad activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(1, 1, '2024-12-15', '10:00:00'),  -- Sabarmati Ashram
(1, 2, '2024-12-15', '15:00:00'),  -- Adalaj Stepwell
(1, 3, '2024-12-16', '16:00:00'),  -- Kankaria Lake
(1, 5, '2024-12-16', '19:30:00');  -- Gujarati Thali

-- Trip 1 Stop 2: Vadodara activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(2, 9, '2024-12-18', '10:00:00'),  -- Laxmi Vilas Palace
(2, 11, '2024-12-18', '16:00:00'); -- Sayaji Garden

-- Trip 2 Stop 2: Bengaluru activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(6, 21, '2024-11-15', '09:00:00'), -- Lalbagh Garden
(6, 22, '2024-11-15', '14:00:00'), -- Bangalore Palace
(6, 23, '2024-11-16', '18:00:00'); -- Craft Beer Tasting

-- Trip 4 Stop 1: Paris activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(10, 42, '2025-03-02', '10:00:00'), -- Eiffel Tower
(10, 43, '2025-03-03', '09:00:00'), -- Louvre Museum
(10, 44, '2025-03-04', '19:00:00'), -- Seine Cruise
(10, 45, '2025-03-05', '11:00:00'); -- Montmartre Tour

-- Trip 4 Stop 2: London activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(11, 46, '2025-03-07', '10:00:00'), -- Tower of London
(11, 47, '2025-03-08', '11:00:00'), -- British Museum
(11, 49, '2025-03-09', '11:00:00'); -- Buckingham Palace

-- Trip 5 Stop 1: Bangkok activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(13, 58, '2024-12-02', '09:00:00'), -- Grand Palace
(13, 59, '2024-12-03', '07:00:00'), -- Floating Market
(13, 61, '2024-12-05', '18:00:00'); -- Street Food Tour

-- Trip 5 Stop 2: Singapore activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(14, 38, '2024-12-09', '10:00:00'), -- Gardens by the Bay
(14, 39, '2024-12-10', '17:00:00'), -- Marina Bay Sands
(14, 41, '2024-12-11', '18:00:00'); -- Hawker Centre

-- Trip 6: Mumbai activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(16, 12, '2024-11-25', '10:00:00'), -- Gateway of India
(16, 13, '2024-11-25', '17:00:00'), -- Marine Drive
(16, 14, '2024-11-26', '18:00:00'); -- Street Food Tour

-- Trip 7: Dubai activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(17, 33, '2025-01-21', '18:00:00'), -- Burj Khalifa
(17, 34, '2025-01-22', '15:00:00'), -- Desert Safari
(17, 35, '2025-01-23', '10:00:00'), -- Dubai Mall
(17, 37, '2025-01-24', '19:30:00'); -- Dubai Fountain

-- Trip 8: Tokyo activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(18, 50, '2025-04-02', '09:00:00'), -- Senso-ji Temple
(18, 51, '2025-04-03', '17:00:00'), -- Shibuya Crossing
(18, 52, '2025-04-04', '06:00:00'), -- Tsukiji Market
(18, 53, '2025-04-05', '14:00:00'); -- Tokyo Skytree

-- Trip 9 Stop 1: Delhi activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(19, 17, '2024-12-05', '10:00:00'), -- Red Fort
(19, 18, '2024-12-05', '16:00:00'), -- India Gate
(19, 19, '2024-12-06', '09:00:00'), -- Chandni Chowk Food Walk
(19, 20, '2024-12-06', '15:00:00'); -- Qutub Minar

-- Trip 9 Stop 3: Jaipur activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(21, 29, '2024-12-10', '09:00:00'), -- Amber Fort
(21, 30, '2024-12-10', '16:00:00'), -- Hawa Mahal
(21, 31, '2024-12-11', '10:00:00'), -- City Palace
(21, 32, '2024-12-11', '15:00:00'); -- Johari Bazaar

-- Trip 10: Goa activities
INSERT INTO trip_activities (trip_stop_id, activity_id, activity_date, activity_time) VALUES
(22, 25, '2024-12-29', '10:00:00'), -- Water Sports
(22, 26, '2024-12-30', '11:00:00'), -- Old Goa Churches
(22, 27, '2024-12-31', '09:00:00'), -- Spice Plantation
(22, 28, '2025-01-01', '18:00:00'); -- Sunset Cruise

-- ====================================================================
-- EXPENSES: 50 expenses across all trips (5 per trip, all categories)
-- All amounts in Indian Rupees (INR/₹)
-- ====================================================================

-- Trip 1: Gujarat Heritage Circuit
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(1, 'transport', 3500.00, 'Train tickets between cities'),
(1, 'stay', 8000.00, 'Hotels across 4 cities'),
(1, 'activities', 1500.00, 'Entry fees and guided tours'),
(1, 'meals', 4000.00, 'Food expenses for 7 days'),
(1, 'other', 1000.00, 'Souvenirs and miscellaneous');

-- Trip 2: South India Temple Tour
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(2, 'transport', 5000.00, 'Bus tickets between cities'),
(2, 'stay', 12000.00, 'Accommodation for 10 days'),
(2, 'activities', 2000.00, 'Temple entry and guided tours'),
(2, 'meals', 6000.00, 'Food expenses'),
(2, 'other', 1500.00, 'Shopping and tips');

-- Trip 3: Rajasthan Royal Experience
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(3, 'transport', 6000.00, 'Car rental for 10 days'),
(3, 'stay', 25000.00, 'Heritage hotels'),
(3, 'activities', 4500.00, 'Palace entry fees and cultural shows'),
(3, 'meals', 8000.00, 'Traditional Rajasthani meals'),
(3, 'other', 3000.00, 'Handicrafts and gifts');

-- Trip 4: European Dream Vacation
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(4, 'transport', 150000.00, 'Flights and trains'),
(4, 'stay', 200000.00, 'Hotels in Paris, London, Rome'),
(4, 'activities', 60000.00, 'Museum tickets and tours'),
(4, 'meals', 80000.00, 'Dining expenses'),
(4, 'other', 30000.00, 'Shopping and souvenirs');

-- Trip 5: Southeast Asia Backpacking
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(5, 'transport', 40000.00, 'Flights between countries'),
(5, 'stay', 30000.00, 'Budget hostels and guesthouses'),
(5, 'activities', 20000.00, 'Tours and experiences'),
(5, 'meals', 15000.00, 'Street food and local restaurants'),
(5, 'other', 10000.00, 'Beach gear and souvenirs');

-- Trip 6: Mumbai Weekend Getaway
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(6, 'transport', 2500.00, 'Local taxis and metro'),
(6, 'stay', 8000.00, 'Hotel for 2 nights'),
(6, 'activities', 1500.00, 'Sightseeing'),
(6, 'meals', 3000.00, 'Food and drinks'),
(6, 'other', 500.00, 'Miscellaneous');

-- Trip 7: Dubai Shopping Festival
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(7, 'transport', 55000.00, 'Flight tickets'),
(7, 'stay', 60000.00, 'Hotel near Dubai Mall'),
(7, 'activities', 40000.00, 'Desert safari and attractions'),
(7, 'meals', 25000.00, 'Restaurants and cafes'),
(7, 'other', 80000.00, 'Shopping at DSF');

-- Trip 8: Japan Cherry Blossom Tour
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(8, 'transport', 80000.00, 'Flight and JR Pass'),
(8, 'stay', 100000.00, 'Hotels in Tokyo'),
(8, 'activities', 30000.00, 'Temples, museums, observation decks'),
(8, 'meals', 40000.00, 'Japanese cuisine'),
(8, 'other', 15000.00, 'Gifts and souvenirs');

-- Trip 9: North India Golden Triangle
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(9, 'transport', 4000.00, 'AC bus tickets'),
(9, 'stay', 15000.00, 'Mid-range hotels'),
(9, 'activities', 3500.00, 'Monument entry fees'),
(9, 'meals', 5000.00, 'Food expenses'),
(9, 'other', 2000.00, 'Marble souvenirs from Agra');

-- Trip 10: Goa Beach Holiday
INSERT INTO expenses (trip_id, category, amount, description) VALUES
(10, 'transport', 8000.00, 'Flight tickets'),
(10, 'stay', 24000.00, 'Beach resort for 6 nights'),
(10, 'activities', 8000.00, 'Water sports and cruises'),
(10, 'meals', 9000.00, 'Beach shacks and restaurants'),
(10, 'other', 3000.00, 'Beachwear and souvenirs');

-- ====================================================================
-- Seed data complete!
-- Final counts:
-- - Users: 51 (1 admin + 50 regular)
-- - Cities: 75 (30 Gujarat + 25 Other India + 20 Foreign)
-- - Activities: 61 (distributed across 15 major cities)
-- - Trips: 10
-- - Trip Stops: 22
-- - Trip Activities: 42
-- - Expenses: 50
-- ====================================================================
