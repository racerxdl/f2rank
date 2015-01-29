#!/usr/bin/python
# coding: utf-8

from fbman import *
from config import *

UTOK = GetToken(TOKEN_FILE)
fb = facebook.GraphAPI(UTOK)
PostMostPlayedSongs(fb)
