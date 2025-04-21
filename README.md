
# Event Site Draft

This repository contains the files for our **Event Planning** project. Below are the instructions on how to set up the project, import the database, and contribute to the project via GitHub.

## 📌 Table of Contents
1. [Database Setup](#database-setup)
2. [How to Commit and Make a Pull Request](#how-to-commit-and-make-a-pull-request)

---

## 💾 Database Setup

To get the database running on your local machine, follow these steps:

### 1. Clone the Repository
Open your terminal (Command Prompt or Git Bash) and clone the repository by running this command:

```bash
git clone https://github.com/24102070/Group2-Final-Project.git
```

This will copy the entire project folder to your computer.

### 2. Open phpMyAdmin
Go to http://localhost/phpmyadmin in your web browser.

### 3. Create a New Database
In phpMyAdmin:

- Click on the **New** button on the left sidebar.
- Name the new database `event_planning`.
- Click **Create**.

### 4. Import the Database SQL File
Now you need to import the database from the .sql file provided in the project:

- Select the `event_planning` database from the list.
- Go to the **Import** tab at the top of the page.
- Under the **File to Import** section, click **Choose File** and select the file `Event_Site_Draft/database/event_site_db.sql` from your local folder.
- Click **Go** to start importing the database.

Once the import finishes, your local phpMyAdmin will have the same database structure and data as the one used in the project.

---

## 📝 How to Commit and Make a Pull Request

When you're working on the project, you'll be making changes to the code. Instead of directly committing your changes to the main branch, you'll need to make a pull request. This is important to ensure that everyone reviews each other's work before it's added to the main project.

Here’s a step-by-step guide on how to do that:

### 1. Commit Changes Locally
a. Open your terminal and go to the project folder:

```bash
cd path/to/your-project-folder
```

b. Make sure you're on the correct branch (use `main` for now unless told otherwise):

```bash
git checkout main
```

c. Add any new or modified files to the staging area:

```bash
git add .
```

This will add all changed files to the next commit. If you only want to add specific files, replace the `.` with the file names.

d. Commit your changes with a descriptive message:

```bash
git commit -m "Describe your changes here"
```

Make sure your commit message explains what you did.

### 2. Push Your Changes to GitHub
a. Push your changes to the remote repository (GitHub):

```bash
git push origin main
```

This will upload your changes to GitHub.

### 3. Make a Pull Request (Important!)
Instead of directly pushing changes to the main branch, it's important to create a pull request (PR). A PR allows other team members to review your changes before they are merged into the main project. Here's how to do it:

a. Go to the GitHub Repository  
Open the repository on GitHub in your web browser.  
You’ll see a banner that says "Your branch is ahead of 'origin/main' by X commits" with a button to **Create Pull Request**.

b. Create a Pull Request  
Click on the "Compare & Pull Request" button.  
Write a description of your changes in the pull request.  
Make sure you choose the correct branch (usually `main`) for merging.  
Click **Create Pull Request**.

c. Wait for Review  
Your teammates will review your changes and leave comments or approve the pull request.  
Once the pull request is approved, it will be merged into the main branch by someone else (or you, depending on permissions).

---

## 🚨 Important: Always Make a Pull Request Instead of Committing Directly to the Main Branch

**Why?**  
A pull request helps ensure that everyone’s code is reviewed before being merged. It also keeps the project organized and avoids conflicts in the code.

**Don't directly push to the main branch!**  
If you push your changes directly to main, it bypasses the review process, and others may end up working with incomplete or broken code.
