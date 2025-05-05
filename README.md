
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
- Under the **File to Import** section, click **Choose File** and select the file `Event_Site_Draft/event_planning.sql` from your local folder.
- Click **Go** to start importing the database.

Once the import finishes, your local phpMyAdmin will have the same database structure and data as the one used in the project.

---

# 🚀 Project Workflow: Save Changes, Commit Only What You Edited, and Make a Pull Request

This guide explains how to properly work with Git in VS Code, ensuring you commit **only the files you changed** and submit your work via a pull request (PR) on GitHub.

---

## 📁 1. Save Your Changes Locally

- Open your project folder in **VS Code**.
- Make the necessary edits (e.g., fix bugs, update UI, add new features).

---

## 🌿 2. Create and Switch to a New Branch

Create a new branch for your changes:

```bash
git checkout -b your-branch-name
```

🔁 Replace `your-branch-name` with something like `fix-login`, `feature-dashboard`, etc.

---

## ✅ 3. Stage and Commit Only the Files You Changed

### 3.1 Stage Your Changes

#### Option A: Using VS Code
- Click the **Source Control (Git)** icon on the sidebar.
- Under **Changes**, click the **+** next to each of the files you edited.

Example changed files:
- `path/to/your-file1.ext`
- `path/to/your-file2.ext`

✅ Stage only the files you modified. Do **not** include unrelated files.

#### Option B: Using Terminal (Better)

```bash
git add path/to/your-file1.ext
git add path/to/your-file2.ext
```

---

### 3.2 Commit Your Changes

#### Option A: Using VS Code
- Enter a descriptive message like:  
  `Updated validation for login form`
- Click the **✔** (checkmark) icon to commit.

#### Option B: Using Terminal (Better)

```bash
git commit -m "your-commit-message"
```

📝 Replace `"your-commit-message"` with a short summary of your update, e.g., `"Fix typo on signup page"`.

---

## ⬆️ 4. Push Your Branch to GitHub

Push your local branch to GitHub:

```bash
git push origin your-branch-name
```

Replace `your-branch-name` with the name you used in Step 2.

---

## 🔁 5. Create a Pull Request (PR)

1. Go to your GitHub repository in a browser.
2. You’ll see a banner:
   ```
   Your recently pushed branches: your-branch-name
   ```
   Click **Compare & Pull Request**.
3. Add a title and description of what you changed.
4. Click **Create Pull Request**.

---

## 👀 6. Review and Approval

- Teammates or reviewers will check your PR.
- Once approved, it will be merged into the `main` branch.

---

## 🎯 7. Final Steps

After your pull request is merged:

```bash
git checkout main
git pull origin main
```

You now have the latest version of the project!

---

## 📌 Tips

- Create **one branch per task or feature**.
- Only commit the files you edited.
- Write clear commit messages.
- Don’t commit directly to `main`.


---

## 🚨 Important: Always Make a Pull Request Instead of Committing Directly to the Main Branch

**Why?**  
A pull request helps ensure that everyone’s code is reviewed before being merged. It also keeps the project organized and avoids conflicts in the code.

**Don't directly push to the main branch!**  
If you push your changes directly to main, it bypasses the review process, and others may end up working with incomplete or broken code.
