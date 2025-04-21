
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

### 1. Save Your Changes Locally
- Open your project folder in **VS Code**.
- Make the necessary changes to your code (e.g., modifying files, adding new ones).

### 2. Stage Your Changes
- In **VS Code**, open the **Source Control** panel by clicking on the Git icon in the sidebar on the left (it looks like a branch).
- You'll see the files you modified listed under **Changes**.
- Click the **+** icon next to each file you want to include in your commit, or click the **+** icon next to **Changes** to stage all modified files.

### 3. Commit Your Changes
- Once your files are staged, enter a commit message in the **Message** box at the top of the Source Control panel.  
  **Example:** `Fixed bug in event registration page`.
- After writing your commit message, click the checkmark icon at the top of the Source Control panel to commit your changes.

### 4. Push Your Changes to GitHub
- Open the **Terminal** in VS Code by going to **Terminal > New Terminal** or using the shortcut `Ctrl + ` (backtick).
- Make sure you're on the correct branch (use `main` unless told otherwise). Type the following in the terminal:
  
  ```bash
  git checkout main
  ```

- Push your changes to GitHub with:

  ```bash
  git push origin main
  ```

### 5. Create a Pull Request
- Open your repository on **GitHub** in your web browser.
- You’ll see a banner that says, "Your branch is ahead of 'origin/main' by X commits" with a button to **Create Pull Request**.
- Click **Compare & Pull Request**.

### 6. Complete the Pull Request
- Add a description of your changes in the pull request.
- Click **Create Pull Request**.
- Wait for your teammates to review and approve your pull request. Once it's approved, it will be merged into the main branch.

---

## 🚨 Important: Always Make a Pull Request Instead of Committing Directly to the Main Branch

**Why?**  
A pull request helps ensure that everyone’s code is reviewed before being merged. It also keeps the project organized and avoids conflicts in the code.

**Don't directly push to the main branch!**  
If you push your changes directly to main, it bypasses the review process, and others may end up working with incomplete or broken code.
