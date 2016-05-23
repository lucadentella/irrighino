#include "config.h"

// get descriptive text for costants

char* getSwitchPositionText(int switchPosition) {
  
  if(switchPosition == 0) return "MANUAL OFF";
  if(switchPosition == 1) return "AUTO"; 
  if(switchPosition == 2) return "MANUAL_ON";  
}

char* getOutputStatusText(int outputStatus) {
  
  if(outputStatus == 0) return "OFF";
  if(outputStatus == 1) return "ON";  
}

char* getRainSensorText(int sensorStatus) {
  
  if(sensorStatus == 0) return "NOT_RAINING";
  if(sensorStatus == 1) return "RAINING";  
}

char* getOutputDescription(int outputId) {

  if(outputId == OUT_1) return "FRONT";
  if(outputId == OUT_2) return "RIGHT";
  if(outputId == OUT_3) return "LEFT";
  if(outputId == OUT_4) return "REAR";
}
