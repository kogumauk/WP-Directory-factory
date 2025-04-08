# Overview
- You are inside a Boilerplate WordPress project, about to start implmenting a new local Business Director site. 
- You are working on a Windows environment, in VS Code, using Powershell.
- This site already has two custom Plugins (uk-location-hierarchy, business-listing-importer), each with seed data in the respective /data/ folder
- The Blocky theme is preinstalled activated. You will need to implement a child-theme based on Blocksy
- Your assignment is to build a modern, sleet, local business Directory website. 
- The <business_niche> is "Fish and Chip shops" in the UK.

# Location hierarchy
- Underpinning this site is <Location_hierarchy> that includes;  <Country> (e.g. England, Wales, Scotland), <Region> (e.g. South East, Midlands, North East), <County/Borough> (e.g. Somerset, Hertfordshire, Camden, Brent), <City/Town> (e.g. St Albans, Watford, Leeds), <Neighbourhood> (e.g. Fleetville), <post_code_prefix> (e.g. AL1,AL2,WF12,LD8)
- There is a custom plugin already created to import this <Location_hierarchy> data

# Business listings
- Each <business_listing> should include Name / Address / Phone (NAP), coordinates, customer reviews, business hours, menu options and prices, social media links, delivery service links, any specialities, dine-in, and any othere meaningful <tags>
- There is already a custom plugin to import this <business_listings> data, ensuring that associations with <Location_hierarchy> is established using the <post_code_prefix>


# Site requirements 

## Layout & Design
- Create a clean, responsive design that prioritizes mobile-first experience while maintaining visual appeal on desktop
- Implement a modern UI with card-based business listings, subtle animations, and intuitive navigation
- Design a prominent search and filter system that appears above the fold on all devices
- Use visual hierarchy to highlight premium business listings without cluttering the interface

## Structure & Organization
- Organize businesses by category, location, and ratings with intuitive taxonomy relationships
- Implement business profile pages with standardized layouts that showcase key information (hours, contact, services)
- Create a logical URL structure that reinforces geographical and categorical relationships
- Design admin dashboard with custom post types for business listings and streamlined metadata fields

## Core Functionality
- Build advanced search functionality with autocomplete, geolocation, and filters (distance, ratings, etc.)
- Implement user review and rating system with verification mechanisms to prevent spam
- Create business owner dashboard for updating listings, responding to reviews, and viewing analytics
- Develop booking/appointment functionality that integrates with popular calendar applications
- Include lead generation forms with tracking capabilities for business owners

## SEO Optimization
- Implement schema markup for local businesses to enhance rich snippets in search results
- Create XML sitemaps with geo-specific data to improve local search visibility
- Generate SEO-optimized meta descriptions and titles based on business data
- Ensure proper canonical tag implementation to manage duplicate location-based content
- Build automated internal linking structure based on geographical and categorical relationships

## Security & Performance
- Implement proper data sanitization and validation for all user inputs (reviews, search queries)
- Ensure GDPR/CCPA compliance for user data collection and business information
- Apply WordPress security best practices including input validation, prepared SQL statements, and output escaping
- Optimize database queries and implement caching strategies to minimize load times
- Create a secure authentication system for business owners accessing their profiles
