FROM registry.access.redhat.com/ubi9/php-82:latest

# Copy application files to the Apache document root
COPY . /var/www/html/

# Expose port 8080 (OpenShift standard)
EXPOSE 8080

# The base image automatically starts httpd, but if needed:
CMD ["httpd", "-D", "FOREGROUND"]
