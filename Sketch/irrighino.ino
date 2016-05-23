// irrighino firmware
// www.lucadentella.it

#define FIRMWARE_VERSION "1.0"

// library includes
#include <Bridge.h>
#include <Process.h>
#include <YunServer.h>
#include <YunClient.h>

// custom includes
#include "config.h"
#include "debug_functions.c"

// global variables
YunServer server;
int switch1Position;
int switch2Position;
int switch3Position;
bool rainSensorStatus;


void setup() {
  
  Serial.begin(9600);
  Bridge.begin();
  Console.begin();
  
  DEBUG_PRINT("Booting irrighino firmware version ");
  DEBUG_PRINTLN(FIRMWARE_VERSION);
  DEBUG_PRINTLN();
  
  // configure PINs
  pinMode(OUT_1, OUTPUT);
  pinMode(OUT_2, OUTPUT);
  pinMode(OUT_3, OUTPUT);
  pinMode(OUT_4, OUTPUT);
  pinMode(LED_1, OUTPUT);
  pinMode(LED_2, OUTPUT);
  pinMode(LED_3, OUTPUT);
  pinMode(LED_4, OUTPUT); 
  pinMode(RAIN_SENSOR, INPUT_PULLUP);
  DEBUG_PRINTLN("- PINs configured");
  
  // default value for variables
  rainSensorStatus = NOT_RAINING;
  switch1Position = SWITCH_AUTO;
  switch2Position = SWITCH_AUTO;
  switch3Position = SWITCH_AUTO;
  switch4Position = SWITCH_AUTO;
  DEBUG_PRINTLN("- Global variables initialized");
  
  // initialize the YunServer to receive commands
  server.listenOnLocalhost();
  server.begin();
  DEBUG_PRINTLN("- YunServer started and listening");  
  
  DEBUG_PRINTLN("- Boot ok, running...");
  DEBUG_PRINTLN();
}

void loop() {
  
  // ---------- CHECK PANEL SWITCHES ----------
  if(USE_SWITCHES) {
  
    int actualSwitchPosition;
    actualSwitchPosition = readSwitchStatus(SWITCH_1);
    if(actualSwitchPosition != switch1Position) {
      updateSwitchPosition(SWITCH_1, actualSwitchPosition);
      switch1Position = actualSwitchPosition;
    }
    actualSwitchPosition = readSwitchStatus(SWITCH_2);
    if(actualSwitchPosition != switch2Position) {
      updateSwitchPosition(SWITCH_2, actualSwitchPosition);
      switch2Position = actualSwitchPosition;
    }
    actualSwitchPosition = readSwitchStatus(SWITCH_3);
    if(actualSwitchPosition != switch3Position) {
      updateSwitchPosition(SWITCH_3, actualSwitchPosition);
      switch3Position = actualSwitchPosition;
    }
  }
  
  // ---------- CHECK RAIN SENSOR ----------
  bool actualRainSensorStatus = digitalRead(RAIN_SENSOR);
  
  // if the new status if different from the previous one, debounce
  if(actualRainSensorStatus != rainSensorStatus) {
    
    delay(50);
    actualRainSensorStatus = digitalRead(RAIN_SENSOR);
    
    // if it's different again, notify
    if(actualRainSensorStatus != rainSensorStatus) {
      
      DEBUG_PRINT("Rain sensor status changed to --> ");
      DEBUG_PRINTLN(getRainSensorText(actualRainSensorStatus));
      
      rainSensorStatus = actualRainSensorStatus;
      updateRainSensorStatus();  
    }
  }
  
  // ---------- CHECK CONSOLE COMMANDS ----------
  if (Console.available() > 0) {
    int incomingByte = Console.read();
    if(incomingByte == '?') printDebugInfo();
  }
  
  // ---------- CHECK YunServer CONNECTIONS ----------
  YunClient client = server.accept(); 
  if (client) {
    String command = client.readStringUntil('/');
    int parameter = client.parseInt();
    DEBUG_PRINT("-> Command ");
    DEBUG_PRINT(command);
    DEBUG_PRINT(" with parameter ");
    DEBUG_PRINTLN(parameter);
    
    String retCode;
    if(command == "on") retCode = executeOutputCommand(parameter, true);
    else if(command == "off") retCode = executeOutputCommand(parameter, false);
    else retCode = "KO_UNKNOWN";
    client.println(retCode);
    client.stop();
  }
}

// Read the switch position
int readSwitchStatus(int switchId) {
  
  int analogValue, actualStatus;
  
  analogValue = analogRead(switchId);
  if(analogValue < 100) actualStatus = SWITCH_MANUAL_OFF;
  else if(analogValue < 900) actualStatus = SWITCH_MANUAL_ON;
  else actualStatus = SWITCH_AUTO;
  
  return actualStatus;
}

// Update the switch position
void updateSwitchPosition(int switchId, int switchPosition) {

  DEBUG_PRINT("-> Switch ");
  DEBUG_PRINT(switchId);
  DEBUG_PRINT(" moved to position ");
  DEBUG_PRINTLN(getSwitchPositionText(switchPosition));
  
  if(switchPosition == SWITCH_MANUAL_OFF) setOutput(getSwitchOutput(switchId), false);
  else if(switchPosition == SWITCH_MANUAL_ON) setOutput(getSwitchOutput(switchId), true);
}


// Turn output (and corresponding led) ON/OFF
void setOutput(int outputId, bool outputStatus) {

  digitalWrite(outputId, outputStatus);
  digitalWrite(getOutputLed(outputId), outputStatus);

  DEBUG_PRINT("-> Output ");
  DEBUG_PRINT(getOutputDescription(outputId));
  DEBUG_PRINT(" set to ");
  DEBUG_PRINTLN(getOutputStatusText(outputStatus));
}


// Execute a command on the outputs received from YunClient
String executeOutputCommand(int outId, bool outputStatus) {
  
  // check if the switch is in AUTO position
  int outputId = getOutputId(outId);
  int switchId = getOutputSwitch(outputId);
  if(getSwitchStatus(switchId) == SWITCH_AUTO) {
    setOutput(outputId, outputStatus);
    return "OK";
  } else return "KO_MANUAL";
}

// Get the output PIN that corresponds to an outId (server-side)
int getOutputId(int outId) {
  
  if(outId == 0) return OUT_1;
  if(outId == 1) return OUT_2;
  if(outId == 2) return OUT_3;  
  if(outId == 3) return OUT_4;   
}

// Get the output that corresponds to a switch
int getSwitchOutput(int switchId) {

  if(switchId == SWITCH_1) return OUT_1;
  if(switchId == SWITCH_2) return OUT_2;
  if(switchId == SWITCH_3) return OUT_3;
  if(switchId == SWITCH_4) return OUT_4;   
}

// Get the switch that corresponds to an output
int getOutputSwitch(int outputId) {

  if(outputId == OUT_1) return SWITCH_1;
  if(outputId == OUT_2) return SWITCH_2;
  if(outputId == OUT_3) return SWITCH_3; 
  if(outputId == OUT_4) return SWITCH_4;
}

// Get the led that corresponds to an output
int getOutputLed(int outputId) {

  if(outputId == OUT_1) return LED_1;
  if(outputId == OUT_2) return LED_2;
  if(outputId == OUT_3) return LED_3;
  if(outputId == OUT_4) return LED_4; 
}

// Get the switch status
int getSwitchStatus(int switchId) {

  if(switchId == SWITCH_1) return switch1Position;
  if(switchId == SWITCH_2) return switch2Position;
  if(switchId == SWITCH_3) return switch3Position;
  if(switchId == SWITCH_4) return switch4Position;
}


// update rain sensor status
void updateRainSensorStatus() {

  Process p;            
  p.begin("php-cli");      
  p.addParameter("/www/sd/irrighino/php/updateRainSensorStatus.php");
  p.addParameter(String(rainSensorStatus));
  p.run();
  
  DEBUG_PRINTLN("Status update sent");
  while (p.available()>0) {
    char c = p.read();
    DEBUG_PRINT(c);
  }
  DEBUG_PRINTLN();
}


// print debug information

void printDebugInfo() {

  DEBUG_PRINT("irrighino firmware version: ");
  DEBUG_PRINTLN(FIRMWARE_VERSION);
  DEBUG_PRINTLN();

  DEBUG_PRINT("Switch 1 position: ");
  DEBUG_PRINTLN(getSwitchPositionText(switch1Position));  
  DEBUG_PRINT("Switch 2 position: ");
  DEBUG_PRINTLN(getSwitchPositionText(switch2Position));  
  DEBUG_PRINT("Switch 3 position: ");
  DEBUG_PRINTLN(getSwitchPositionText(switch3Position));
  DEBUG_PRINT("Switch 4 position: ");
  DEBUG_PRINTLN(getSwitchPositionText(switch4Position));  
  DEBUG_PRINTLN();
  
  DEBUG_PRINT("Rain sensor status: ");
  DEBUG_PRINTLN(getRainSensorText(rainSensorStatus));
}
