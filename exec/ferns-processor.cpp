#include <iostream>
#include <string>
#include <sys/time.h>
#include "cv.h"
#include "highgui.h"
#include <opencv2/calib3d/calib3d.hpp>
#include <opencv2/features2d/features2d.hpp>

using namespace cv;
using namespace std;

int main(int argc, char** argv) {
	struct timeval start, end;
	
	int number_of_warps = 100;
	int cornerTarget = 200;
	string input_filename;
  string output_directory;
	bool verbose = false;
	bool nozip = false;

	// Required parameters are input file and output diretory
	// More than that means number_of_warps and/or cornerTarget were specified
	if(argc > 1) {
		bool inputSet = false;
		for(int i=1; i<argc; i++) {
			if(strcmp(argv[i], "-warps") == 0) {
				if(i+1 < argc) {
					number_of_warps = atoi(argv[++i]);
				}
			}
			else if(strcmp(argv[i], "-features") == 0) {
				if(i+1 < argc) {
					cornerTarget = atoi(argv[++i]);
				}
			}
			else if(strcmp(argv[i], "-v") == 0 || strcmp(argv[i], "-verbose") == 0) {
				verbose = true;
			}
			else if(strcmp(argv[i], "-nozip") == 0) {
				nozip = true;
			}
			else if(!inputSet) {
				input_filename = string(argv[i]);
				inputSet = true;
			}
			else {
				output_directory = string(argv[i]);
			}
		}
	}
	else {
		cout << "Error: not enough arguments.";
		return -1;
	}

	// Get file basename
	size_t last_path = input_filename.find_last_of("/\\");
	if(last_path == input_filename.npos) last_path = -1;
	string basename = input_filename.substr(last_path + 1);
	size_t last_dot = basename.find_last_of(".");
	basename = basename.substr(0,last_dot);

	// Set up output files
	string debug_filename	= output_directory + '/' + basename + "-debug.jpg";
	string fern_filename		= output_directory + '/' + basename;
	fern_filename += nozip ? ".xml" : ".xml.gz";

	if(verbose) {
		cout << "Input file: " << input_filename << "\n";
		cout << "Output dir: " << output_directory << "\n";
		cout << "Base name:  " << basename << "\n";
		cout << "Debug file: " << debug_filename << "\n";
		cout << "Fern file:  " << fern_filename << "\n";
	}

	Mat img, grayImg;
	vector<KeyPoint> FASTCorners;
	int FASTThreshold = 40;

	img = imread(input_filename.c_str());
	cvtColor(img, grayImg, CV_RGB2GRAY);
	
	// Adaptive threshold on input image to find ~200 corners
	float threshDelta;
	while(fabs(FASTCorners.size() - cornerTarget) > cornerTarget * .1){
		FAST(grayImg, FASTCorners, FASTThreshold, true);
		threshDelta = (float)(FASTCorners.size() - cornerTarget) * 0.01;
		if(threshDelta > 0 && threshDelta < 1) threshDelta = 1;
		if(threshDelta < 0 && threshDelta > -1) threshDelta = -1;
		FASTThreshold += threshDelta;
		//cout << "Delta: " << threshDelta << "\n";

		//cout << "Corners found: " << FASTCorners.size() << "\t\tThreshold: " <<
		//	FASTThreshold << "\n"; 
	}
	if(verbose)
		cout << "Found " << FASTCorners.size() << " corner features.\n";

	// Draw keypoints over original image
	Scalar color(0,256,0);
	for(int i=0; i<FASTCorners.size(); i++) {
		circle(img, FASTCorners[i].pt, 2, color, -1);
	}

	imwrite(debug_filename, img);

	if(verbose) {
		cout << "Starting Ferns classifier...\n";
		cout << number_of_warps << " warps.\n";
	}
	gettimeofday(&start, NULL);
	FernClassifier fern;
	PatchGenerator patchGen(0,256,5,true,0.2,1.5,-CV_PI/4,CV_PI/4,
													-CV_PI/1,CV_PI/2);
	fern.setVerbose(verbose);

	fern.trainFromSingleView(grayImg,FASTCorners,32,(int)FASTCorners.size(),
														20, 10, number_of_warps,
														FernClassifier::COMPRESSION_NONE,
														patchGen);
	// Write Fern output
	FileStorage fs(fern_filename, FileStorage::WRITE);
	if(fs.isOpened()) {
		WriteStructContext ws(fs, basename, CV_NODE_MAP);
		cv::write(fs, "model-points", FASTCorners);
		fern.write(fs, "fern-classifier");
		if(verbose) cout << "Fern file written. \n";
	}
	else {
		cout << "Error: Could not write fern file.";
		return -1;
	}
	
	if(verbose) {
		cout << "Done!\n";
		gettimeofday(&end, NULL);
		cout << ((end.tv_sec - start.tv_sec)*1000 +
						(end.tv_usec - start.tv_usec)/1000)/1000. << "\n";
	}
	cout << fern_filename;
	return 0;
}
