# encoding: utf-8

from Crypto.Util.number import *
from Crypto.Random.random import *
import sys
from flag import FLAG

def str2int(s):
    return int(s.encode("hex"), 16)

def int2hexstr(n):
    s = "{:0x}".format(n)
    if len(s) % 2 == 1:
        s = "0" + s
    return s

def int2str(n):
    return int2hexstr(n).decode("hex")

def genParams():
    p = getPrime(512)
    q = getPrime(512)
    n = p * q
    g = n + 1  # where sizeof p == sizeof q

    l = (p - 1) * (q - 1)
    mu = inverse(l, n)

    return (n, g, l, mu)

def decrypt(n, l, mu, c):
    n2 = n * n
    return ((pow(c, l % n2, n2) - 1) * mu / n) % n

def main():
    try:
        n, g, l, mu = genParams()

        print("--- Homomorphic Cryptosystem ---")
        print("n:{}".format(n))
        print("g:{}".format(g))
        print("l:{}".format(l))
        print("µ:{}".format(mu))
        sys.stdout.write("Your name>>")
        sys.stdout.flush()
        c1 = raw_input()
        c1 = int(c1, 16)

        m1 = decrypt(n, l, mu, c1)
        if int2str(m1) == "pascal_paillier":
            print("Hello father!")
            print(FLAG)
            return

        m1 = str2int("Takoyakitabetai")

        c = (c1 * pow(g, m1, n*n)) % (n*n)
        m = decrypt(n, l, mu, c)
        name = int2str(m)

        print("Hello {}!".format(name))
        with open("kings_of_the_hill", "a") as f:
            f.write(name + "\n")
    except Exception as e:
        print(e)
        sys.stderr.write(str(e) + "\n")
        sys.stdout.flush()

if __name__ == '__main__':
    main()
