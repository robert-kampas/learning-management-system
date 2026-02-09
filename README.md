# Setup

## Starting Containers

**Mac ARM64 (M1/M2/M3):**
```bash
docker compose up -d
```

**Other architectures:**
1. Build the image: `docker build -t learning-management-system .`
2. Edit `docker-compose.yml`:
    - Comment: `image: rkampas/learning-management-system:latest`
    - Uncomment: `image: learning-management-system`
3. Start containers: `docker compose up -d`

## Application Setup

1. **Install dependencies:**
```bash
   sh install.sh
```
The script outputs the application URL.

2. **Initialize database:**
```bash
   sh setup.sh
```
The script outputs example endpoint URLs.

**Troubleshooting:** If scripts fail, open them and run commands manually.
