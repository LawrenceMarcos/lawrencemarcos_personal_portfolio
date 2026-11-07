# Lawrence Marcos — Personal Portfolio

**Live website:** [Open my portfolio](https://lawrencemarcos.infinityfree.me/)  

A personal portfolio website introducing me, showing selected school projects, and providing a contact form.

## About

I am a BS in Information Technology student at Pamantasan ng Lungsod ng Valenzuela. I am interested in networking and am continuing to build my technical skills through coursework and projects.

## Website Features

- About section with my education and interests
- Project section featuring two school projects
- Light and dark appearance modes
- Responsive layout for desktop and mobile screens
- Contact form with field validation and email delivery

## Featured Projects

### NOVO Branch Network Infrastructure Design

A Cisco Packet Tracer group project presenting a network infrastructure design that connects three branches.

### Warehouse Performance Tracker Analyzer

A PHP group project sample for reviewing warehouse product performance. It includes daily performance analysis, weekly averages, product classifications, and stock status information.

## Technologies Used

- **HTML** — page structure
- **CSS** — layout, responsive styling, and light/dark themes
- **JavaScript** — interactive features and asynchronous form submission
- **PHP** — server-side contact form validation and forwarding valid messages
- **Formspree** — delivery of contact form messages to my email

## Contact Form Flow

1. A visitor enters their name, email address, and message.
2. JavaScript sends the form to the PHP page without reloading the website.
3. PHP checks the required fields, email format, input lengths, and spam-check field.
4. PHP forwards valid submissions to Formspree.
5. The form displays a success message after Formspree accepts the submission.

## Run Locally

You need [XAMPP](https://www.apachefriends.org/) or another PHP server. PHP must have the cURL extension enabled for the contact form to forward messages.

1. Copy the project folder into XAMPP’s `htdocs` folder.
2. Start Apache in the XAMPP Control Panel.
3. Open `http://localhost/<project-folder/` in your browser.

## Project Structure

```text
project-folder/
├── assets/          # Portrait and project images
├── css/
│   └── style.css    # Website styles
├── index.php        # Portfolio page and contact form processing
└── README.md        # Project information and setup guide
