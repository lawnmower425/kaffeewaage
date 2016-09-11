import RPi.GPIO as GPIO
import time
import sys
import pprint

GPIO.setmode(GPIO.BCM)

def createBoolList(size=8):
	ret = []
	for i in range(8):
		ret.append(False)
	return ret


class HX711:
	
	def __init__(self, dout, pd_sck, gain=128):
		self.PD_SCK = pd_sck
		self.DOUT = dout
		
		GPIO.setup(self.PD_SCK, GPIO.OUT)
		GPIO.setup(self.DOUT, GPIO.IN)
		
		self.GAIN = 0
		self.OFFSET = 0
		self.SCALE = 1
		self.lastVal = 0
		
		self.set_gain(gain);
	
	#Der hx711 ist nicht immer sofort bereit fuer read	
	def is_ready(self):
		return GPIO.input(self.DOUT) == 0
	
	def set_gain(self, gain):
		if gain is 128:
			self.GAIN = 1
		elif gain is 64:
			self.GAIN = 3
		elif gain is 32:
			self.GAIN = 2
	
		GPIO.output(self.PD_SCK, False)
		self.read()
		
	def read(self):
		while not self.is_ready():
			#print("WAITING");
			pass
		#Ein warten verbessert die Readouts - das Ding ist nicht sehr schnell
		time.sleep(0.2);	
	
		reneVal = 0
		for x in range(23):
			GPIO.output(self.PD_SCK, True)
			reneVal = (reneVal << 1) | GPIO.input(self.DOUT)
			GPIO.output(self.PD_SCK, False)

		
		#set channel and gain factor for next reading - siehe datasheet
		for i in range(self.GAIN):
			GPIO.output(self.PD_SCK, True);
			GPIO.output(self.PD_SCK, False);
		
		self.lastVal = reneVal

		return reneVal
		
	def read_average(self, times=3):
		sum = 0
		for i in range(times):
			sum += self.read()
		
		return sum / times;
	
	def get_value(self, times=3):
		return self.read_average(times) - self.OFFSET
	
	def get_units(self, times=3):
		return self.get_value(times) / self.SCALE
	
	def tare(self, times=15):
		sum = self.read_average(times);
		self.set_offset(sum)
	
	def set_scale(self, scale):
		self.SCALE = scale
	
	def set_offset(self, offset):
		self.OFFSET = offset
	
	def power_down(slef):
		GPIO.output(self.PD_SCK, False)
		GPIO.output(self.PD_SCK, True)
	
	def power_up(self):
		GPIO.output(self.PD_SCK, False)

if (len(sys.argv) <= 1):
	print("Bitte Befehl angeben!")
	sys.exit()

sOrder = sys.argv[1]

if sOrder == "read":
	hx = HX711(21, 17)
	hx.set_scale(3500)
	print(hx.get_units(3))
	GPIO.cleanup()
	sys.exit()
