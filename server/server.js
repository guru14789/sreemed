const express = require('express');
const mongoose = require('mongoose');
const cors = require('cors');
require('dotenv').config();

const app = express();
const port = process.env.PORT || 5000;

const corsOptions = {
  origin: 'https://3000-firebase-sreemeditecgit-1756351109196.cluster-bg6uurscprhn6qxr6xwtrhvkf6.cloudworkstations.dev',
  optionsSuccessStatus: 200
};

app.use(cors(corsOptions));
app.use(express.json());

const uri = process.env.MONGO_URI;
mongoose.connect(uri, { useNewUrlParser: true, useUnifiedTopology: true });
const connection = mongoose.connection;
connection.once('open', () => {
  console.log("MongoDB database connection established successfully");
})

app.use('/api/users', require('./routes/user'));
app.use('/api/products', require('./routes/products'));
app.use('/api/orders', require('./routes/orders'));

app.get('/', (req, res) => {
  res.send('Hello from the MERN stack!');
});

app.listen(port, () => {
  console.log(`Server is running on port: ${port}`);
});
