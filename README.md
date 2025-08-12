# TeamSpike-WP

**TeamSpike-WP** is a custom WordPress theme developed specifically for the TeamSpike project.  
The theme is designed for scalability and extensibility, featuring custom post types, helpful utility functions, and a well-structured frontend setup.

---

## 📁 Project Structure

.
├── front-page.php # Main page template
├── functions.php # Core theme modules loader
├── includes/
│ ├── assets.php # Registration and enqueue of styles and scripts
│ ├── custom-post-types.php # Definition of custom post types
│ ├── helpers.php # Helper functions for templates
│ ├── theme-setup.php # Theme initialization and WordPress features support

---

## ⚙️ Key Features

- **Custom Post Types**  
  Defined in `includes/custom-post-types.php`, allowing you to extend WordPress standard functionality to manage project-specific entities.

- **Styles and Scripts Management**  
  All necessary CSS and JS files are registered and enqueued in `includes/assets.php`.

- **Helper Functions**  
  Utility functions located in `includes/helpers.php` simplify working with templates and theme features.

- **Theme Setup**  
  Basic theme settings such as thumbnail support, menus, post formats, and other WordPress features are configured in `includes/theme-setup.php`.

---

## 🚀 Installation

1. Clone the repository into your WordPress themes directory:

```bash
git clone https://github.com/Karataev-Daniil/TeamSpike-WP.git
Activate the theme from the WordPress admin dashboard:

Appearance → Themes → TeamSpike-WP → Activate
🛠 Requirements
WordPress version 6.x

PHP version 7.4+

MySQL version 5.7+ or MariaDB 10.3+

📄 License
This project is licensed under the MIT License.