# Project Management System

The Project Management System is a web-based application that facilitates efficient project management, collaboration, and task tracking for teams and individuals. It provides a range of features tailored to different user roles, including Team Managers, Project Managers, and Employees. The system aims to streamline project workflows, enhance communication, and improve overall productivity.

## Features

### Common Features

- **PDF Downloads**: Employees can download PDF reports for specific projects, containing all the associated task details, enabling easy sharing and offline access.
- **Filtering System**: Users can filter tasks and projects based on criteria such as status, priority, or assigned team members, simplifying search and navigation.
- **Profile Updates**: Users can update their profile information, including personal details, contact information, and profile picture, ensuring accurate and up-to-date user profiles.
- **Check-In/Check-Out**: Users can check in and check out, providing visibility into their working hours and attendance.
- **Dashboard Chart Representations**: The dashboard includes various chart representations for project progress, task status, and user performance, offering visual insights for better decision-making.

### Team Manager Panel (TM)

- **Dashboard**: TM can view an analytical graph of all projects and user activation status, providing an overview of team activities.
- **Users**: TM can create, update, read, and delete user accounts, manage approvals, and filter users by name or email.
- **Projects**: TM can view all projects within their department, filter projects, view project details, and download project reports.
- **Requested Employee**: TM can accept or reject requests from project managers to assign specific users to projects, ensuring proper resource allocation.
- **Check-In/Check-Out Time**: TM can monitor employee attendance and work hours by viewing check-in and check-out times.
- **Impersonate Employee Account**: TM can temporarily access employee accounts for troubleshooting, support, or information retrieval.

### Employee Panel

- **Chart and Analytics**: Employees can view charts representing hours worked, project status, and task status, providing insights into their performance.
- **Task and Project Management**: Employees can view assigned tasks and projects, track time, and collaborate with team members.
- **Check-In/Check-Out**: Employees can check in and check out daily, ensuring accurate attendance records.
- **Kanban Board**: Employees can use a Kanban board to visualize and manage tasks within projects, facilitating task organization and progress tracking.

### Login Features

- **Microsoft Office Integration**: Users can log in via Microsoft Office, automatically populating profile details from their account.
- **Direct Login**: Users can log in directly and manage their profile photo within the system.
- **Google Login**: Users can log in using their Google account for seamless access.

### Additional Features

- **Password Reset**: Users can reset their password via email using a password reset link.
- **Two-Factor Authentication**: Users receive an OTP on their registered mobile number for enhanced security.
- **Check-In/Check-Out**: Users are required to check in and check out daily for attendance tracking.

### Project Manager Panel

- **Dashboard**: Project Managers can view project and task status through graphs and charts.
- **Projects**: Project Managers can manage projects, view project details, and download reports.
- **Create Project**: Project Managers can create new projects and attach relevant documents.
- **Manage Project**: Project Managers can edit project details, add team members, and manage project tasks.
- **Add Team Members**: Project Managers can request to add employees to their project team.
- **Approval or Rejection**: Project Managers receive notifications for approving or rejecting team member requests.
- **Create Task**: Project Managers can create tasks, assign multiple users, and set task details.
- **Manage Task**: Project Managers can view and edit task details within projects.

## Installation

To set up the Project Management System locally, follow these steps:

1. Clone the repository: `git clone https://github.com/your-username/project-management-system.git`
2. Navigate to the project directory: `cd project-management-system`
3. Install dependencies: `yarn install`
4. Configure the database connection in `a.env`
5. Import the database schema from `symfony console make:migration`
6. Import the database schema from `symfony console doctrine:migration:migrate
7. Start the development server: `Yarn build` and `yarn watch`
8. Start the development server: `symfony serve`  
9. Access the application in your web browser: `http://localhost:3000`

Please make sure you have PHP and a compatible web server installed on your machine.

## Technologies Used

- HTML5, CSS3, JavaScript
- PHP
- MySQL
- jQuery
- Bootstrap
- ChartJS

## Contributing

We welcome contributions from everyone. If you'd like to contribute to the project, please follow these steps:

1. Fork the repository
2. Create a new branch: `git checkout -b feature-name`
3. Make your changes and commit them: `git commit -am 'Add some feature'`
4. Push to the branch: `git push origin feature-name`
5. Submit a pull request


## Contact
For any inquiries or support, please contact us at kartikpartelswami@gmail.com.
