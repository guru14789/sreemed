import express from 'express';
const router = express.Router();
import { register, login, getMe } from '../controllers/userController.js';
import { protect } from '../middleware/auth.js';

router.post('/register', register);
router.post('/login', login);
router.get('/me', protect, getMe);

export default router;
