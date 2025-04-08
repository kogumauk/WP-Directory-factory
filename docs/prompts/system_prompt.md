# Overview
- You are inside a blank WordPress project, about to start implmenting a new site. 
- You are working on a Windows environment, in VS Code, using Powershell.
- Your assignment is to build a modern, sleet, local business Directory website. 
- The <business_niche> is "Fish and Chip shops" in the UK.

# Location hierarchy
- Underpinning this site is <Location_hierarchy> that includes;  <Country> (e.g. England, Wales, Scotland), <Region> (e.g. South East, Midlands, North East), <County/Borough> (e.g. Somerset, Hertfordshire, Camden, Brent), <City/Town> (e.g. St Albans, Watford, Leeds), <Neighbourhood> (e.g. Fleetville), <post_code_prefix> (e.g. AL1,AL2,WF12,LD8)
- There is a location_hierarchy_seed.JSON file in e.g. the docs/seed_data/ directory for this purpose 
- You will also need to create a custom plugin to import this <Location_hierarchy> data, based on the structure found
- Upon import, the plugin must create everything needed ready for integration with <business_listings>.

# Business listings
- Each <business_listing> should include Name / Address / Phone (NAP), coordinates, customer reviews, business hours, menu options and prices, social media links, delivery service links, any specialities, dine-in, and any othere meaningful <tags>
- There is a business_listings_seed.JSON file in e.g. the docs/seed_data/ directory for this purpose 
- You will also need to create a custom plugin to import this <business_listings> data, ensuring that associations with <Location_hierarchy> is established, based on the <post_code_prefix>


# Site specifics
- All <tags> and <Location_hierarchy> to be searchable and filterable
- The site should be optimised for SEO, including a <blog> section, a <contact> section, an <FAQ> section, Best <category> in <location> pages
- The overall site structureshould be sleekand modern, with a <header>, a compelling <hero_section>, 4 or 5 horizontal sections, a <footer> etc
- The site should be responsive and work beatifully on Mobile as well as Web. 
- Use a child-theme based on Blocksy



 
