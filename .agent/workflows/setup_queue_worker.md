---
description: How to set up and monitor Laravel Queue Workers using Supervisor on a VPS
---

# Setup Laravel Queue Worker (Supervisor)

To ensure your asynchronous jobs (like withdrawals and trades) run continuously in the background, you need to set up a process monitor. We recommend **Supervisor**.

## 1. Install Supervisor

On your Ubuntu/Debian VPS:

```bash
sudo apt-get update
sudo apt-get install supervisor
```

## 2. Create Configuration File

Create a new configuration file for your Laravel worker:

```bash
sudo nano /etc/supervisor/conf.d/laravel-worker.conf
```

Paste the following content. **IMPORTANT**: Replace `/path/to/your/project` with your actual project path (e.g., `/var/www/cryptomart`) and `user` with your server username (e.g., `www-data` or `ubuntu`).

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/your/project/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/path/to/your/project/storage/logs/worker.log
stopwaitsecs=3600
```

*   `numprocs=2`: Runs 2 worker processes in parallel.
*   `--max-time=3600`: Restarts the worker every hour to prevent memory leaks.
*   `user=www-data`: Ensure this user has permission to write to `storage/logs`.

## 3. Apply Changes

Run the following commands to load the new configuration and start the workers:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start laravel-worker:*
```

## 4. Verify Status

Check if the workers are running:

```bash
sudo supervisorctl status
```

You should see output like:
`laravel-worker:laravel-worker_00   RUNNING   pid 12345, uptime 0:00:05`

## 5. Deployment Note

Whenever you deploy new code (like the jobs we just created), you **MUST** restart the queue to load the new code:

```bash
php artisan queue:restart
```

# Troubleshooting

If jobs are stuck:
1.  Check logs: `tail -f storage/logs/worker.log` (if configured) or `storage/logs/laravel.log`.
2.  Ensure `.env` has `QUEUE_CONNECTION=database`.
3.  Check failed jobs: `php artisan queue:failed`.
4.  Retry failed jobs: `php artisan queue:retry all`.
