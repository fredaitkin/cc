import 'dotenv/config';
import express from 'express';
import { runMigrations } from './db';
import membersRouter from './routes/members';
import choresRouter from './routes/chores';
import instancesRouter from './routes/instances';

const app = express();
const PORT = parseInt(process.env.PORT ?? '3001', 10);

app.use(express.json());

app.use('/api/members', membersRouter);
app.use('/api/chores', choresRouter);
app.use('/api/instances', instancesRouter);

runMigrations()
  .then(() => {
    app.listen(PORT, () => {
      console.log(`API listening on http://localhost:${PORT}`);
    });
  })
  .catch(err => {
    console.error('Failed to run migrations:', err);
    process.exit(1);
  });
