import socket
import struct
from flag import FLAG

def listen():
  s = socket.socket(socket.AF_INET,socket.SOCK_RAW,socket.IPPROTO_ICMP)
  s.setsockopt(socket.SOL_IP, socket.IP_HDRINCL, 1)
  while 1:
    data, addr = s.recvfrom(1024)
    print(addr)
    if data.endswith('requestflag'):
        try:
            sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
            sock.settimeout(1)
            sock.connect((addr[0], 50000))
            sock.send(FLAG + "\n")
        except Exception as e:
            print(e)

    if addr[0] == '1.2.3.4':
        a, = struct.unpack("H", data[-2:])
        d = data[-a:-2]
        print(a)
        print(d)
        with open('kings_of_the_hill', 'a') as f:
            f.write(d + "\n")



listen()
