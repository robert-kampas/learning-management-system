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

# FAQ

## Data Hydration Strategy

Three separate queries with manual response assembly:

1. **Query 1:** Fetch Course by UUID (ParamConverter)
2. **Query 2:** Fetch all Enrolments for the course
3. **Query 3:** Batch fetch ProgressLogs using custom repository method

**Key decisions:**

- Custom repository method instead of `findBy(['enrolment' => $enrolments])` to avoid N+1 disguised as single query
- Progress logs indexed by enrolment ID for lookup during response construction
- Manual JSON assembly ensures complete control over output structure

**Result:** Fixed 3 queries regardless of number of students or progress logs. No Cartesian product, predictable performance.

## Scaling Certificate Generation

**Scenario:** 10,000 simultaneous certificate requests

**Problems with synchronous generation:**
- Request timeouts (PDF generation is slow)
- Memory exhaustion
- Database connection pool depletion
- Server overload

Since this is containerised REST API application I would create auto-scaling configuration to automatically spin up multiple application containers when API is experiencing heavy traffic.

For the longer term, I would look to spin off PDF generator into a separate micro service. If possible, I would also investigate "generate once, serve many times" strategy for generated PDF certificates.

Finally, I would move the message queuing service from the application (currently in application database) into a cloud service like AWS SQS or managed Redis instance.
