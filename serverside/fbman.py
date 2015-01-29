#!/usr/bin/python
# coding: utf-8

import facebook
import urllib 
import json

from config import *

def GetToken(tokenfile):
    f = open(tokenfile)
    UTOK = f.read()
    f.close()
    return UTOK

def GetMostPlayedSongs():
    url = "%s?page=api&cmd=mostplayedsongs"%SITE_URL
    data = json.loads(urllib.urlopen(url).read())
    if data["status"] == "OK":
        return data["data"]
    else:
        return False 
        
def GetMostPlayedCharts():
    url = "%s?page=api&cmd=mostplayedcharts"%SITE_URL
    data = json.loads(urllib.urlopen(url).read())
    if data["status"] == "OK":
        return data["data"]
    else:
        return False 
        
def GetTop100EXP():
    url = "%s?page=api&cmd=exptop100"%SITE_URL
    data = json.loads(urllib.urlopen(url).read())
    if data["status"] == "OK":
        return data["data"]
    else:
        return False 

def GetTop100Score():
    url = "%s?page=api&cmd=scoretop100"%SITE_URL
    data = json.loads(urllib.urlopen(url).read())
    if data["status"] == "OK":
        return data["data"]
    else:
        return False 
        
def PostMostPlayedSongs(fb):
    data = GetMostPlayedSongs()
    if  data != False:
        attach = {
            "name": 'Most Played Songs',
            "link": '%s?page=mostplayedsongs'%SITE_URL,
            "caption": 'First Place: %s - %s'%(data[0]["artist"],data[0]["name"]),
            "description": 'Total Plays: %s'%data[0]["totalplays"],
            "picture" : '%s/img/titles/%s.jpg'%(SITE_URL,data[0]["songid"])
        }     
        message = "This is the 5 most played songs of this month!\n\n"
        for i in range(0,5):
            if len(data) < i:
                break
            else:
                message += " #%s | %s - %s\n"%(data[i]["place"],data[i]["artist"],data[i]["name"])
        fb.put_wall_post(message, attachment=attach,profile_id=FACEBOOK_PROFILE_ID )
        
def PostMostPlayedCharts(fb):
    data = GetMostPlayedCharts()
    if  data != False:
        attach = {
            "name": 'Most Played Charts',
            "link": '%s?page=mostplayedcharts'%SITE_URL,
            "caption": 'First Place: %s - %s (%s%s)'%(data[0]["artist"],data[0]["name"],data[0]["mode"],data[0]["level"]),
            "description": 'Total Plays: %s'%data[0]["totalplays"],
            "picture" : '%s/img/titles/%s.jpg'%(SITE_URL,data[0]["songid"])
        }     
        message = "This is the 5 most played charts of this month!\n\n"
        for i in range(0,5):
            if len(data) < i:
                break
            else:
                message += " #%s | %s - %s (%s%s)\n"%(data[i]["place"],data[i]["artist"],data[i]["name"],data[i]["mode"],data[i]["level"])
        fb.put_wall_post(message, attachment=attach,profile_id=FACEBOOK_PROFILE_ID )
        
def PostEXP100Charts(fb):
    data = GetTop100EXP()
    if  data != False:
        attach = {
            "name": 'EXP TOP 100 Users',
            "link": '%s?page=top100exp'%SITE_URL,
            "caption": 'First Place: %s (Level %s)'%(data[0]["name"],data[0]["level"]),
            "description": 'Experience: %s'%data[0]["score"],
            "picture" : '%s/img/avatar/CH_%03d.PNG'%(SITE_URL,data[0]["avatar"]+1)
        }     
        message = "This is the TOP 5 Experience this month!\n\n"
        for i in range(0,5):
            if len(data) < i:
                break
            else:
                message += " #%s | %s - %s (Level %s)\n"%(i+1,data[i]["name"],data[i]["score"],data[i]["level"])
        fb.put_wall_post(message, attachment=attach,profile_id=FACEBOOK_PROFILE_ID )

def PostScore100Charts(fb):
    data = GetTop100Score()
    if  data != False:
        attach = {
            "name": 'Arcade Score TOP 100',
            "link": '%s?page=top100score'%SITE_URL,
            "caption": 'First Place: %s (Level %s)'%(data[0]["name"],data[0]["level"]),
            "description": 'Score: %s'%data[0]["score"],
            "picture" : '%s/img/avatar/CH_%03d.PNG'%(SITE_URL,data[0]["avatar"]+1)
        }     
        message = "This is the TOP 5 Arcade Scores this month!\n\n"
        for i in range(0,5):
            if len(data) < i:
                break
            else:
                message += " #%s | %s - %s (Level %s)\n"%(i+1,data[i]["name"],data[i]["score"],data[i]["level"])
        fb.put_wall_post(message, attachment=attach,profile_id=FACEBOOK_PROFILE_ID )       
