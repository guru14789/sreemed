
# Instructions to Fix MongoDB Connection Error

Your application is failing because it cannot connect to your MongoDB database. To fix this, you need to provide your database connection string.

## Steps:

1.  **Get your connection string from MongoDB Atlas:**
    *   Log in to your [MongoDB Atlas account](https://cloud.mongodb.com/).
    *   Find your cluster and click the **"Connect"** button.
    *   Select **"Connect your application"** (or "Drivers").
    *   Copy the connection string provided. It will look like this:
        ```
        mongodb+srv://<username>:<password>@<cluster-url>/<database-name>?retryWrites=true&w=majority
        ```

2.  **Update your `.env` file:**
    *   Open the file `server/.env`.
    *   Paste your connection string into this file.
    *   **Crucially, replace `<password>` with your actual database user's password.** The file should contain one line:
        ```
        MONGO_URI=mongodb+srv://myuser:mypassword@mycluster.mongodb.net/mydatabase?retryWrites=true&w=majority
        ```

3.  **Tell me when you are done!**
    Once you have saved your connection string in `server/.env`, let me know, and I will restart the server for you.
