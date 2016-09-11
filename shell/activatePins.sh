#Beim zero lohnt siche sudo rpi-update, da möglicherweise noch nicht /dev/gpiomem verwendet wird
#Und wenn das nicht verwendet wird, dann gibt es Zugriffsefehler
#In dem Falle: ad
#Setup basic stuff
chown root:gpio /dev/gpiomem
adduser pi gpio
echo "17" > /sys/class/gpio/unexport
echo "27" > /sys/class/gpio/unexport


echo "17" > /sys/class/gpio/export
echo "in" > /sys/class/gpio/gpio17/direction
echo "27" > /sys/class/gpio/export
echo "in" > /sys/class/gpio/gpio27/direction
