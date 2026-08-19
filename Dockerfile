FROM registry.access.redhat.com/ubi9/php-82:latest

# Copy application source code to the container working directory
COPY . /opt/app-root/src

# Set permissions if necessary
RUN fix-permissions /opt/app-root

# Expose port 8080 (OpenShift standard non-root port)
EXPOSE 8080

CMD ["run-httpd"]

