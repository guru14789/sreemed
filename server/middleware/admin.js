import User from '../models/User.js';

const admin = async (req, res, next) => {
  if (req.user && req.user.isAdmin) {
    next();
  } else {
    res.status(403).json({ message: 'Access denied. Not an admin.' });
  }
};

export { admin };
