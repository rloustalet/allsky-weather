PYTHON = python3
PIP = pip3
SYSTEMD_DIR = /etc/systemd/system
INSTALL_DIR = /home/$(USER)/allsky

.PHONY: install
install: install_packages install_files install_service enable_service

.PHONY: install_packages
install_packages:
	sudo $(PIP) install flask flask_cors smbus bme680

.PHONY: install_files
install_files:
	cp -r html $(INSTALL_DIR)/html
	cp -r scripts $(INSTALL_DIR)/scripts

.PHONY: install_service
install_service:
	cp weather.service $(SYSTEMD_DIR)

.PHONY: enable_service
enable_service:
	systemctl enable weather.service

.PHONY: uninstall
uninstall:
	systemctl disable weather.service
	rm $(SYSTEMD_DIR)/weather.service
	rm -rf $(INSTALL_DIR)

.PHONY: clean
clean:
	# Add any additional cleanup commands if needed
