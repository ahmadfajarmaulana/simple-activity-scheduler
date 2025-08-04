# Outdoor Activity Scheduler

A simple internal web app built with Laravel to help users schedule outdoor activities by checking the weather forecast from BMKG (Indonesia’s Meteorology Agency).

---

## Features

- Search for village/sub-district (kelurahan/desa) with autocomplete
- Submit preferred outdoor activity date & location
- Automatically suggest best time slot based on BMKG weather data
- Display 3-day weather forecast
- Show warning if all 3 days have bad weather
- Track and list recently scheduled activities

---

## Screenshot

![Screenshot](screenshot.png)

---

## 🏗️ Tech Stack

- **Laravel 12**
- **BMKG Weather API**
- **Select2** for searchable dropdown
- **MySQL** (or any Laravel-compatible database)
- PHP 8.2+

---

## 🛠️ Setup Instructions

1. **Clone the repo**
   ```bash
   git clone https://github.com/your-username/outdoor-activity-scheduler.git
   cd outdoor-activity-scheduler
