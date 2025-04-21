-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 03:37 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `event_planning`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `package_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `company_id`, `schedule_id`, `status`, `created_at`, `package_id`) VALUES
(11, 8, 6, 10, 'cancelled', '2025-04-17 08:06:41', 9),
(12, 8, 6, 10, 'cancelled', '2025-04-17 08:16:41', 9),
(13, 8, 6, 10, 'accept', '2025-04-17 08:41:37', 9),
(14, 10, 6, 10, 'reject', '2025-04-17 08:55:41', 9),
(15, 11, 6, 12, 'accept', '2025-04-17 09:15:17', 9),
(16, 8, 8, 15, 'accept', '2025-04-20 12:13:23', 10);

-- --------------------------------------------------------

--
-- Table structure for table `companies`
--

CREATE TABLE `companies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `cert_file` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `minimum_fee` decimal(10,2) NOT NULL,
  `status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `approval` enum('Pending','Approved') NOT NULL DEFAULT 'Pending',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `companies`
--

INSERT INTO `companies` (`id`, `name`, `email`, `cert_file`, `description`, `minimum_fee`, `status`, `approval`, `password`) VALUES
(6, ' EverAfter Events & Planning', 'eventafter@gmail.com', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Fundle - Brochure .pdf', 'EverAfter Events & Planning is a full-service event planning company that specializes in organizing, designing, and executing events tailored to your unique needs. We offer both partial and full event coordination services for weddings, birthdays, corporate functions, and more. With a keen eye for detail and a love for storytelling, we ensure that every event is as seamless as it is spectacular.', 13000.00, 'Available', 'Approved', '$2y$10$.yWfZsIWntZBH9NTIh9bn./IqhF434Afhv6z2PUTe18zMVIc76Zsq'),
(8, 'Elite Event Planners', 'contact@eliteeventplanners.com', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/ilovepdf_merged (3) (2).pdf', 'Elite Event Planners specializes in creating unforgettable experiences for every occasion. Whether it\'s a wedding, corporate event, or private party, our team provides full-service event planning, including venue selection, catering, entertainment, and custom decor. We are committed to delivering exceptional events that exceed client expectations with a personal touch.', 7000.00, 'Available', 'Approved', '$2y$10$V9huGsMRSWEo55.3.vURKu9iFJuYtYPkvfRwYzkxISNo9t4XJKLPu'),
(9, 'bilat', 'bilay@gmail.com', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/ilovepdf_merged (3) (2) (1).pdf', 'coochieemeowmeow dildo seller', 70000.00, 'Available', '', '$2y$10$IgAmLXPBex14c0iyt1FlYe2qbA7d5zg95W4WYMDdFAf4EWeWK63k.');

-- --------------------------------------------------------

--
-- Table structure for table `company_posts`
--

CREATE TABLE `company_posts` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `caption` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_posts`
--

INSERT INTO `company_posts` (`id`, `company_id`, `caption`, `media_path`, `media_type`, `created_at`) VALUES
(5, 6, 'We’re beyond excited to share that Sophia & Elijah Reyes have trusted us to bring their dream wedding to life! 💍💐 Their vision is set, and we’re on our way to making every detail perfect for their special day. 🌟\r\n\r\nWith our The Dream Day package, we’re managing every little detail to ensure their wedding is flawless from start to finish. From selecting the perfect venue to coordinating with vendors, we\'re here to create a seamless and unforgettable celebration! 💖\r\n\r\n💍 Package Inclusions:\r\n\r\nFull wedding planning and coordination: PHP 50,000\r\n\r\nVenue selection and booking: PHP 30,000\r\n\r\nTheme and design consultation: PHP 20,000\r\n\r\nVendor management (catering, photography, florists, etc.): PHP 30,000\r\n\r\nOn-the-day coordination: PHP 20,000\r\n\r\n💰 Total Price: PHP 150,000\r\n\r\nWe can’t wait to make this couple’s big day absolutely magical! ✨💖\r\n\r\n#WhimsyWorksByLuna #DreamWedding #SophiaAndElijah #WeddingPlanning #FlawlessWedding #DreamDayPackage #MakingDreamsComeTrue #WeddingGoals', 'uploads/67fe74f17207b.jpg', 'image', '2025-04-15 15:00:15'),
(6, 8, '🌟 Make Your Event Unforgettable with Elite Event Planners! 🌟\r\n\r\nAt Elite Event Planners, we specialize in turning your vision into reality, no matter the occasion. Whether you\'re planning a luxurious wedding, a corporate gathering, or a private party, our team is here to make it extraordinary. ✨\r\n\r\nWhat we offer: ✅ Custom Event Design & Coordination\r\n✅ Venue Selection\r\n✅ Catering with Custom Menus\r\n✅ Entertainment Options (Live Bands, DJs & More!)\r\n✅ Event Photography & Videography\r\n✅ Tailored Decor to Match Your Theme\r\n\r\nOur mission is simple: to create stress-free, memorable events that exceed your expectations.\r\n\r\nLet us handle the details so you can enjoy your special day! 💍🎉\r\n\r\n💌 DM us or call [Your Contact Number] to book a free consultation.\r\n\r\n#EliteEventPlanners #EventPlanning #UnforgettableEvents #WeddingPlanning #CorporateEvents #PartyPlanning #EventSuccess\r\n\r\n', 'uploads/6804e3c719e64.jpg', 'image', '2025-04-20 12:08:39'),
(7, 8, '🎉 Another Successful Corporate Event by Elite Event Planners! 🎉\r\n\r\nWe are beyond thrilled to have been a part of this incredible corporate event! From seamless coordination to stunning decor and top-notch catering, everything came together perfectly. 👔✨\r\n\r\nIt was a pleasure working with [Client’s Company Name] to deliver a memorable experience that reflected their company’s values and vision. Here\'s a quick glimpse of what we helped create:\r\n\r\n✅ Flawless Event Setup & Branding\r\n✅ Interactive Team-Building Activities\r\n✅ Delicious Custom Catering\r\n✅ Professional Photography to Capture Every Moment\r\n✅ Smooth Logistics & Transportation\r\n\r\nA big thank you to our amazing team and clients for making this event a huge success! 🙌\r\n\r\nLooking to elevate your next corporate event? We’ve got you covered. Let’s create something unforgettable together! 💼🎤\r\n\r\n📩 DM us or contact [Your Contact Number] to start planning your event today.\r\n\r\n#CorporateEventSuccess #EliteEventPlanners #EventCoordination #TeamBuilding #CorporateEventPlanning #EventSuccess #ClientAppreciation', 'uploads/6804e443b2e8c.jpg', 'image', '2025-04-20 12:10:43');

-- --------------------------------------------------------

--
-- Table structure for table `company_profiles`
--

CREATE TABLE `company_profiles` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'default_profile.jpg',
  `cover_photo` varchar(255) DEFAULT 'default_cover.jpg',
  `about` text DEFAULT NULL,
  `contact` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_profiles`
--

INSERT INTO `company_profiles` (`id`, `company_id`, `profile_photo`, `cover_photo`, `about`, `contact`) VALUES
(4, 6, 'uploads/edc6dffa-6a79-4b37-9c37-ef43a98af6b3.jpg', 'uploads/villa-millagros-intimate-dusty-blue-wedding-008.1.jpg', 'i am gay', '0928645330'),
(5, 8, 'uploads/elite.jfif', 'uploads/elitecover.jpg', 'At Elite Event Planners, we believe that every event should be as unique and memorable as the people who attend it. With years of experience in the event planning industry, we specialize in crafting unforgettable experiences for every occasion. Whether it’s a wedding, corporate event, or intimate private party, our dedicated team is here to bring your vision to life.\r\n\r\nOur services include:\r\n\r\nVenue Selection: We help you find the perfect venue that matches your style and budget.\r\n\r\nCatering: Offering customized menus that cater to a variety of tastes and dietary preferences.\r\n\r\nEntertainment: From live bands to DJs and performers, we ensure your guests are entertained throughout the event.\r\n\r\nCustom Decor: Tailored decorations to match your theme, with a keen eye for detail to make every event truly special.\r\n\r\nWhat sets us apart is our commitment to delivering not just events, but experiences that exceed client expectations. Our personal touch ensures that every event is a true reflection of our client’s needs and desires.\r\n\r\nAt Elite Event Planners, your event is more than just another project; it’s our passion.', '0945 672 8947');

-- --------------------------------------------------------

--
-- Table structure for table `company_reviews`
--

CREATE TABLE `company_reviews` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_reviews`
--

INSERT INTO `company_reviews` (`id`, `company_id`, `package_id`, `user_id`, `rating`, `review`, `created_at`) VALUES
(1, 6, 9, 8, 4, 'usto ko nang bumitaw', '2025-04-20 08:38:59'),
(2, 6, 9, 11, 5, 'I love it', '2025-04-20 09:05:15'),
(3, 6, 9, 10, 4, 'Superbb! Enjoyed lots', '2025-04-20 09:05:52'),
(4, 6, 9, 12, 5, 'The wedding was astonishing, would highly recommend! Its so skibidi', '2025-04-20 09:08:07'),
(5, 6, 9, 13, 2, 'This sucks!!!', '2025-04-21 03:54:32');

-- --------------------------------------------------------

--
-- Table structure for table `company_schedules`
--

CREATE TABLE `company_schedules` (
  `id` int(11) NOT NULL,
  `company_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `company_schedules`
--

INSERT INTO `company_schedules` (`id`, `company_id`, `date`, `start_time`, `end_time`) VALUES
(6, 6, '2025-04-17', '08:55:00', '09:55:00'),
(7, 6, '2025-04-17', '14:57:00', '15:58:00'),
(8, 6, '2025-04-17', '07:20:00', '08:28:00'),
(9, 6, '2025-04-18', '17:23:00', '19:25:00'),
(10, 6, '2025-04-23', '18:23:00', '19:23:00'),
(12, 6, '2025-04-30', '08:12:00', '09:12:00'),
(13, 8, '2025-04-29', '08:11:00', '10:12:00'),
(14, 8, '2025-04-30', '10:11:00', '11:12:00'),
(15, 8, '2025-04-30', '12:11:00', '13:17:00');

-- --------------------------------------------------------

--
-- Table structure for table `freelancers`
--

CREATE TABLE `freelancers` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `resume_file` varchar(255) NOT NULL,
  `profession` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `minimum_fee` decimal(10,2) NOT NULL,
  `status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `approval` enum('Pending','Approved') NOT NULL DEFAULT 'Pending',
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancers`
--

INSERT INTO `freelancers` (`id`, `name`, `email`, `resume_file`, `profession`, `description`, `minimum_fee`, `status`, `approval`, `password`) VALUES
(8, 'Luna Rivera', 'lunamakes@gmail.com', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Full (1).pdf', 'Photographer', 'Specializes in natural light event photography with a documentary style. From candid smiles to styled portraits, she captures every meaningful detail.', 8000.00, 'Available', 'Approved', '$2y$10$2fONsnysIU/IQ5r8/ysBBeF62/C8RcUXvT.neRWulfyFTxNAjF6qG'),
(9, 'Isabella Cruz', 'isabakes.designs@gmail.com', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Quiz-on-VLSM.pdf', 'Cake Designer', 'I specialize in custom cake designs for weddings, birthdays, and special events. From elegant tiered cakes to fun and creative themed designs, each cake is handcrafted with attention to detail and flavor. I offer consultations to personalize each order to your theme, dietary preferences, and occasion needs. I also provide edible toppers, cupcakes, and dessert tables on request.', 1900.00, 'Available', 'Approved', '$2y$10$mzSwdWdnTb97IjPAWE6F2OeKSZ42GnfY2suOIGtYfwIvD3VAr8neG');

-- --------------------------------------------------------

--
-- Table structure for table `freelancers_review_ratings`
--

CREATE TABLE `freelancers_review_ratings` (
  `id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `package_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(1) NOT NULL,
  `review` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancers_review_ratings`
--

INSERT INTO `freelancers_review_ratings` (`id`, `freelancer_id`, `package_id`, `user_id`, `rating`, `review`, `created_at`) VALUES
(1, 8, 5, 8, 4, 'Would Highly Recommend!', '2025-04-18 14:44:47'),
(2, 8, 5, 11, 5, 'i love it!\r\n', '2025-04-18 15:03:18'),
(3, 8, 5, 12, 5, 'am lovin it like an alpha sigmaa skibidi toilet', '2025-04-20 09:17:38'),
(4, 8, 5, 10, 3, 'Mid\r\n', '2025-04-20 09:57:38');

-- --------------------------------------------------------

--
-- Table structure for table `freelancer_bookings`
--

CREATE TABLE `freelancer_bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `status` varchar(20) DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `package_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer_bookings`
--

INSERT INTO `freelancer_bookings` (`id`, `user_id`, `freelancer_id`, `schedule_id`, `status`, `created_at`, `package_id`) VALUES
(1, 10, 8, 6, 'pending', '2025-04-18 12:55:33', 5),
(2, 8, 8, 6, 'accept', '2025-04-18 13:13:04', 5),
(3, 13, 8, 4, 'pending', '2025-04-21 03:56:27', 5);

-- --------------------------------------------------------

--
-- Table structure for table `freelancer_posts`
--

CREATE TABLE `freelancer_posts` (
  `id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `caption` text NOT NULL,
  `media_path` varchar(255) DEFAULT NULL,
  `media_type` enum('image','video') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer_posts`
--

INSERT INTO `freelancer_posts` (`id`, `freelancer_id`, `caption`, `media_path`, `media_type`, `created_at`) VALUES
(1, 8, '🎉 A Day to Remember — Through My Lens 📸\r\n\r\nYesterday\'s birthday celebration was nothing short of magical — full of laughter, tight hugs, and sweet, candid moments that made my job feel like a privilege. Being there to witness and capture all the love shared between family and friends reminded me why I do what I do.\r\n\r\nEvery giggle, every surprise, every joyful tear — frozen in time for them to look back on and smile.\r\n\r\nThis session was part of my Capture It package, designed for events just like this — where memories deserve to be held onto forever.\r\n\r\n📷 Captured by Luna Rivera\r\n#CaptureItByLuna #FreelancePhotographer #BirthdayMoments #ThroughMyLens #EventPhotography', 'uploads/68025a71afc88.jpg', 'image', '2025-04-18 13:58:09');

-- --------------------------------------------------------

--
-- Table structure for table `freelancer_profiles`
--

CREATE TABLE `freelancer_profiles` (
  `id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `profile_photo` varchar(255) DEFAULT 'default_profile.jpg',
  `cover_photo` varchar(255) DEFAULT 'default_cover.jpg',
  `about` text DEFAULT NULL,
  `contact` varchar(255) NOT NULL,
  `minimum_fee` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer_profiles`
--

INSERT INTO `freelancer_profiles` (`id`, `freelancer_id`, `profile_photo`, `cover_photo`, `about`, `contact`, `minimum_fee`) VALUES
(3, 8, 'uploads/images.jfif', 'uploads/images (1).png', 'Hi, I’m Luna — the hands, heart, and hustle behind Whimsy Works! I’m an independent creative who helps bring your event dreams to life, one detail at a time. Whether it’s capturing real moments through my lens, baking sweet centerpieces that steal the show, or styling setups that pop on Instagram, I do it all with love and flair.\r\n\r\nWith years of experience in both intimate and big events, I make sure every project I touch adds that little spark of magic your occasion deserves.\r\n\r\n', '09195604322', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `freelancer_schedules`
--

CREATE TABLE `freelancer_schedules` (
  `id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `freelancer_schedules`
--

INSERT INTO `freelancer_schedules` (`id`, `freelancer_id`, `date`, `start_time`, `end_time`) VALUES
(1, 8, '2025-04-18', '22:47:00', '23:47:00'),
(2, 8, '2025-04-17', '11:53:00', '12:53:00'),
(4, 8, '2025-04-26', '23:45:00', '12:45:00'),
(5, 8, '2025-04-25', '23:45:00', '12:45:00'),
(6, 8, '2025-04-30', '13:58:00', '14:58:00');

-- --------------------------------------------------------

--
-- Table structure for table `packages`
--

CREATE TABLE `packages` (
  `id` int(11) NOT NULL,
  `company_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `inclusions` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `freelancer_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages`
--

INSERT INTO `packages` (`id`, `company_id`, `name`, `details`, `inclusions`, `price`, `created_at`, `image`, `freelancer_id`) VALUES
(9, 6, 'The Dream Day (Wedding)', 'The Dream Day package is designed for couples who want to experience a flawless wedding from start to finish. With full coordination, we will manage every detail to bring your dream wedding to life—from choosing the perfect venue to handling vendors and ensuring everything runs smoothly on your special day.', 'Full wedding planning and coordination: PHP 50,000\r\n\r\nVenue selection and booking: PHP 30,000\r\n\r\nTheme and design consultation: PHP 20,000\r\n\r\nVendor management (catering, photography, florists, etc.): PHP 30,000\r\n\r\nOn-the-day coordination: PHP 20,000', 150000.00, '2025-04-15 13:42:57', 'uploads/67fe626167dee.jpg', NULL),
(10, 8, 'Premium Wedding Package', 'Our Premium Wedding Package is designed for couples who want a luxurious and stress-free wedding experience. We offer a full range of services from venue selection to personalized decor, ensuring that every detail of your big day is perfectly executed.', 'Venue Selection: Assistance in finding and securing the ideal venue for your wedding.\r\nPrice: PHP 40,000\r\n\r\nCatering: Customized menu with appetizers, main courses, and dessert options, including dietary accommodations.\r\nPrice: PHP 60,000\r\n\r\nEntertainment: A live band or DJ for music throughout the event, with a personalized playlist.\r\nPrice: PHP 20,000\r\n\r\nCustom Decor: Tailored decorations to match the theme, including centerpieces, floral arrangements, and lighting.\r\nPrice: PHP 30,000\r\n\r\nEvent Coordination: On-site coordinator to ensure everything runs smoothly on the day of the wedding.\r\nPrice: PHP 15,000\r\n\r\nPhotography & Videography: A professional photographer and videographer to capture every moment.\r\nPrice: PHP 25,000\r\n\r\nBridal Suite Access: Complimentary access to a bridal suite for relaxation before the ceremony.\r\nPrice: PHP 5,000\r\n\r\nTransportation: Luxury car service for the bride and groom, with options for guests.\r\nPrice: PHP 10,000', 150000.00, '2025-04-20 12:04:16', 'uploads/image-2c40b17f-e118-4c04-b488-4d0a53c04d6e.jpg', NULL),
(11, 8, 'Corporate Event Excellence Package', 'Our Corporate Event Excellence Package is designed to provide a seamless and professional event experience for businesses looking to host conferences, seminars, or team-building events. From elegant venue arrangements to top-notch catering, we ensure your event reflects your company\'s values and goals.', 'Venue Selection: Assistance in finding and securing a venue that suits your event size and corporate branding.\r\nPrice: PHP 25,000\r\n\r\nCatering: Buffet or plated meals with a selection of appetizers, main courses, and beverages, including options for vegetarian and dietary needs.\r\nPrice: PHP 40,000\r\n\r\nEvent Setup & Branding: Custom signage, branded decorations, and stage setup to reflect your company’s identity.\r\nPrice: PHP 20,000\r\n\r\nAudio-Visual Equipment: Rental of microphones, projectors, screens, and sound systems for presentations or performances.\r\nPrice: PHP 15,000\r\n\r\nEvent Coordination: A dedicated event manager to ensure the smooth flow of the event, from registration to conclusion.\r\nPrice: PHP 18,000\r\n\r\nEntertainment/Activities: Team-building activities or entertainment options like live music, games, or interactive sessions.\r\nPrice: PHP 25,000\r\n\r\nPhotography: Professional photographer to capture key moments and create lasting memories of your corporate event.\r\nPrice: PHP 10,000\r\n\r\nTransportation: Shuttle services for guests to and from the venue, ensuring a hassle-free experience.\r\nPrice: PHP 7,000', 160000.00, '2025-04-20 12:07:35', 'uploads/6804e387b0c10.jpg', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `packages_freelancers`
--

CREATE TABLE `packages_freelancers` (
  `id` int(11) NOT NULL,
  `freelancer_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `details` text NOT NULL,
  `inclusions` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `packages_freelancers`
--

INSERT INTO `packages_freelancers` (`id`, `freelancer_id`, `name`, `details`, `inclusions`, `price`, `image`, `created_at`, `updated_at`) VALUES
(5, 8, 'Capture It – Photography Package', 'The \"Capture It\" photography package is perfect for those looking to preserve memories in a creative and personal way. This package includes everything you need for beautiful event coverage and stunning photos.', 'Inclusions:\r\n\r\n2 hours of event coverage: Capture all the key moments of your event, whether it\'s a wedding, birthday, or special celebration.\r\n\r\n50+ edited photos: A collection of high-quality, professionally edited photos to cherish for years to come.\r\n\r\nOnline gallery delivery: Your photos will be delivered in a secure online gallery, easy to view and share with family and friends.\r\n\r\nAdd-ons:\r\n\r\nExtra hours: Add more coverage for PHP 2,000 per hour.\r\n\r\nPrinted photo book: A beautiful, professionally printed photo book to showcase your memories for PHP 4,500.\r\n\r\nPre-event shoot: Capture the excitement before your event with a pre-event photoshoot for PHP 6,000.', 20500.00, 'uploads/67fe64bad4448.jpg', '2025-04-15 13:52:58', '2025-04-15 13:52:58');

-- --------------------------------------------------------

--
-- Table structure for table `providers`
--

CREATE TABLE `providers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `business_info` text NOT NULL,
  `contact_info` varchar(100) DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `NAME` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('user','company','freelancer','admin') NOT NULL DEFAULT 'user',
  `contact_number` varchar(15) NOT NULL,
  `valid_id_file` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `NAME`, `email`, `password`, `created_at`, `role`, `contact_number`, `valid_id_file`) VALUES
(8, 'Drixyl', 'drixyl.nacu@gmail.com', '$2y$10$OKlhM5VCbo5mZG3cQ8Go9OvIqpzIR9KVOu0fI7lIRLcngY.vXYquy', '2025-04-15 12:51:41', 'user', '', ''),
(9, 'Nathan Alinsug', 'nathanalinsug@gmail.com', '$2y$10$huntlKzYu5zi.BErKmCdr.0O/kYwt5FQfBrB7Tl3ak7GdUGv0.iO2', '2025-04-15 12:52:23', 'admin', '', ''),
(10, 'Diesel Nacu', 'dieselnacu@gmail.com', '$2y$10$/YJIeJPzjmWc0cdoV3nTVevyfvPceGSiWsCFYA2Vlv3ygwWTvetPC', '2025-04-17 08:55:12', 'user', '09283307744', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Screenshot 2025-03-27 160417.png'),
(11, 'Ian Florentino', 'ianmiguel@gmail.com', '$2y$10$dtnivaHWdVKlCFqiCI.jY...hhBMSiGFPaVm3gRdnB2coq2vs70ey', '2025-04-17 09:02:38', 'user', '09286607766', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Frame.png'),
(12, 'RJ Nacu', 'rjnacu@gmail.com', '$2y$10$e2w/6yctLH/QwRIJen5ocOqY5l2AbVP99b21OpxpG5mJ005zPFqAK', '2025-04-20 09:07:05', 'user', '0928842567', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/ilovepdf_merged (3) (1).pdf'),
(13, 'Osbev Cabucos', 'osbevcabucos@gmail.com', '$2y$10$ZbC6HxjZahOoVlWJlJoXuujekgCr7eAOFKHO6EnVs0VaCau2YlgeO', '2025-04-21 03:53:39', 'user', '09181436969', 'C:\\xampp\\htdocs\\Event_Site_Draft\\auth/../uploads/Colorful Abstract Dancing Image Dance Studio Logo.png');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `bookings_ibfk_2` (`company_id`);

--
-- Indexes for table `companies`
--
ALTER TABLE `companies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `company_posts`
--
ALTER TABLE `company_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `company_reviews`
--
ALTER TABLE `company_reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_review` (`company_id`,`package_id`,`user_id`);

--
-- Indexes for table `company_schedules`
--
ALTER TABLE `company_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `freelancers`
--
ALTER TABLE `freelancers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `freelancers_review_ratings`
--
ALTER TABLE `freelancers_review_ratings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `freelancer_id` (`freelancer_id`),
  ADD KEY `package_id` (`package_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `freelancer_bookings`
--
ALTER TABLE `freelancer_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `freelancer_id` (`freelancer_id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `package_id` (`package_id`);

--
-- Indexes for table `freelancer_posts`
--
ALTER TABLE `freelancer_posts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `freelancer_profiles`
--
ALTER TABLE `freelancer_profiles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `freelancer_schedules`
--
ALTER TABLE `freelancer_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `packages`
--
ALTER TABLE `packages`
  ADD PRIMARY KEY (`id`),
  ADD KEY `company_id` (`company_id`);

--
-- Indexes for table `packages_freelancers`
--
ALTER TABLE `packages_freelancers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `freelancer_id` (`freelancer_id`);

--
-- Indexes for table `providers`
--
ALTER TABLE `providers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `companies`
--
ALTER TABLE `companies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `company_posts`
--
ALTER TABLE `company_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `company_profiles`
--
ALTER TABLE `company_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `company_reviews`
--
ALTER TABLE `company_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `company_schedules`
--
ALTER TABLE `company_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `freelancers`
--
ALTER TABLE `freelancers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `freelancers_review_ratings`
--
ALTER TABLE `freelancers_review_ratings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `freelancer_bookings`
--
ALTER TABLE `freelancer_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `freelancer_posts`
--
ALTER TABLE `freelancer_posts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `freelancer_profiles`
--
ALTER TABLE `freelancer_profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `freelancer_schedules`
--
ALTER TABLE `freelancer_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `packages`
--
ALTER TABLE `packages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `packages_freelancers`
--
ALTER TABLE `packages_freelancers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `providers`
--
ALTER TABLE `providers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`),
  ADD CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `company_schedules` (`id`);

--
-- Constraints for table `company_posts`
--
ALTER TABLE `company_posts`
  ADD CONSTRAINT `company_posts_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_profiles`
--
ALTER TABLE `company_profiles`
  ADD CONSTRAINT `company_profiles_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `company_schedules`
--
ALTER TABLE `company_schedules`
  ADD CONSTRAINT `company_schedules_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `freelancers_review_ratings`
--
ALTER TABLE `freelancers_review_ratings`
  ADD CONSTRAINT `freelancers_review_ratings_ibfk_1` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id`),
  ADD CONSTRAINT `freelancers_review_ratings_ibfk_2` FOREIGN KEY (`package_id`) REFERENCES `packages_freelancers` (`id`),
  ADD CONSTRAINT `freelancers_review_ratings_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `freelancer_bookings`
--
ALTER TABLE `freelancer_bookings`
  ADD CONSTRAINT `freelancer_bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `freelancer_bookings_ibfk_2` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id`),
  ADD CONSTRAINT `freelancer_bookings_ibfk_3` FOREIGN KEY (`schedule_id`) REFERENCES `freelancer_schedules` (`id`),
  ADD CONSTRAINT `freelancer_bookings_ibfk_4` FOREIGN KEY (`package_id`) REFERENCES `packages_freelancers` (`id`);

--
-- Constraints for table `freelancer_profiles`
--
ALTER TABLE `freelancer_profiles`
  ADD CONSTRAINT `fk_freelancer_id` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_freelancer_profile` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `packages`
--
ALTER TABLE `packages`
  ADD CONSTRAINT `packages_ibfk_1` FOREIGN KEY (`company_id`) REFERENCES `companies` (`id`);

--
-- Constraints for table `packages_freelancers`
--
ALTER TABLE `packages_freelancers`
  ADD CONSTRAINT `packages_freelancers_ibfk_1` FOREIGN KEY (`freelancer_id`) REFERENCES `freelancers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
