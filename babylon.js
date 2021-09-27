var canvas = document.getElementById("renderCanvas"); // Get the canvas element
var engine = new BABYLON.Engine(canvas, true); // Generate the BABYLON 3D engine
/******* Add the create scene function ******/
        var createScene = function () {
			 var cad=<?php include('test.json'); ?>;
            // Create the scene space
            var scene = new BABYLON.Scene(engine);

            // Add a camera to the scene and attach it to the canvas
            var camera = new BABYLON.ArcRotateCamera("Camera", Math.PI / 2, Math.PI / 2, 2, new BABYLON.Vector3(0,0,5), scene);
            camera.attachControl(canvas, true);

            // Add lights to the scene
            var light1 = new BABYLON.HemisphericLight("light1", new BABYLON.Vector3(1, 1, 0), scene);
            var light2 = new BABYLON.PointLight("light2", new BABYLON.Vector3(0, 1, -1), scene);

			
            // Add and manipulate meshes in the scene
            var sphere = BABYLON.MeshBuilder.CreateSphere("sphere", {diameter:2}, scene);
			
			sphere.position = new BABYLON.Vector3(2, 3, 4);
			var myMaterial = new BABYLON.StandardMaterial("myMaterial", scene);
			myMaterial.diffuseColor = new BABYLON.Color3(1, 0, 1);
			myMaterial.alpha=0.5;
			myMaterial.wireframe=true;         
			sphere.material=myMaterial;
			
			
			var myMaterial1 = new BABYLON.StandardMaterial("myMaterial1", scene);
			myMaterial1.diffuseColor = new BABYLON.Color3(1, 0, 0);

            //loading mesh directly
			var myMat = new BABYLON.StandardMaterial("myMat", scene);
			myMat.diffuseColor = new BABYLON.Color3(1, 1, 0);
			var m = new BABYLON.Mesh("mf",scene);
			var positions=Array();
			 for (var i=0; i<cad.vertices.length; i++)
			 {
			  positions.push(cad.vertices[i].x);
			  positions.push(cad.vertices[i].y);
			  positions.push(cad.vertices[i].z);
			 }
			var vertexData = new BABYLON.VertexData();
			vertexData.positions = positions;
			vertexData.indices = cad.triangles;
			vertexData.applyToMesh(m);
			m.material=myMat;
			var maxxyz=Math.max(m.getBoundingInfo().boundingBox.extendSize.x,m.getBoundingInfo().boundingBox.extendSize.y,m.getBoundingInfo().boundingBox.extendSize.z)
			camera.radius=maxxyz*3;
			//end of direct mesh loading
			
			var myBox = BABYLON.MeshBuilder.CreateBox("myBox", {height: 5, width: 2, depth: 0.5}, scene);
			myBox.material=myMaterial1;
			var sourcePlane = new BABYLON.Plane(0, -1, 1, 0);
			sourcePlane.normalize();
			var myPlane = BABYLON.MeshBuilder.CreatePlane("myPlane", {width: 5, height: 2, sourcePlane: sourcePlane}, scene);
			
			BABYLON.SceneLoader.ImportMesh("","./", "blenderbodytop.stl", scene, function (newMeshes) {
				// do something with the new mesh
				var myMaterial2 = new BABYLON.StandardMaterial("myMat", scene);
				myMaterial2.diffuseColor = new BABYLON.Color3(1, 0, 0);
				newMeshes[0].material=myMaterial2;
			});
			
			
			sphere.actionManager = new BABYLON.ActionManager(scene);
			myBox.actionManager = new BABYLON.ActionManager(scene);
			
			var makeOverOut = function (mesh) {
				mesh.actionManager.registerAction(new BABYLON.InterpolateValueAction(BABYLON.ActionManager.OnPointerOutTrigger, mesh.material, "diffuseColor", mesh.material.diffuseColor,300));
				mesh.actionManager.registerAction(new BABYLON.InterpolateValueAction(BABYLON.ActionManager.OnPointerOverTrigger, mesh.material, "diffuseColor", BABYLON.Color3.White(),300));
				}
				
			var registerClick = function (mesh) {
				mesh.actionManager.registerAction(new BABYLON.ExecuteCodeAction(BABYLON.ActionManager.OnPickTrigger, 
					function(event){
						var pickedMesh = event.meshUnderPointer; 
						window.alert("Hello");
					})
					);
			}
				
			makeOverOut(sphere);
			makeOverOut(myBox);
			registerClick(sphere);

			/*
			BABYLON.SceneLoader.ImportMesh("","./", "blenderbottom.stl", scene, function (newMeshes) {
				// do something with the new mesh
				var myMaterial3 = new BABYLON.StandardMaterial("myMat", scene);
				myMaterial3.diffuseColor = new BABYLON.Color3(0, 0.6, 0);
				newMeshes[0].material=myMaterial3;
				
				var makeOverOut = function (mesh) {
				mesh.actionManager.registerAction(new BABYLON.SetValueAction(BABYLON.ActionManager.OnPointerOutTrigger, mesh.material, "diffuseColor", mesh.material.emissiveColor));
				mesh.actionManager.registerAction(new BABYLON.SetValueAction(BABYLON.ActionManager.OnPointerOverTrigger, mesh.material, "diffuseColor", BABYLON.Color3.White()));
				}

				makeOverOut(newMeshes[0]);
			});
			*/
            return scene;
        };
        /******* End of the create scene function ******/

		/******* Add the create scene function ******/
        var createSceneScrew = function (cad) {
            // Create the scene space
            var scene = new BABYLON.Scene(engine);
            // Add a camera to the scene and attach it to the canvas
            var camera = new BABYLON.ArcRotateCamera("Camera", Math.PI / 2, Math.PI / 2, 2, new BABYLON.Vector3(0,0,1), scene);                                   
            camera.attachControl(canvas, true);

            // Add lights to the scene
            var light1 = new BABYLON.HemisphericLight("light1", new BABYLON.Vector3(1, 1, 0), scene);
            var light2 = new BABYLON.PointLight("light2", new BABYLON.Vector3(0, 1, -1), scene);
			
			
			BABYLON.SceneLoader.Append("", "data:" + JSON.stringify(cad), scene, function (scene) {
				// do something with the scene
			});
            //loading mesh directly
			/*
			var myMat = new BABYLON.StandardMaterial("myMat", scene);
			myMat.diffuseColor = new BABYLON.Color3(1, 1, 0);
			var m = new BABYLON.Mesh("mf",scene);
			var positions=Array();
			 for (var i=0; i<cad.vertices.length; i++)
			 {
			  positions.push(cad.vertices[i].x);
			  positions.push(cad.vertices[i].y);
			  positions.push(cad.vertices[i].z);

			 }
			var vertexData = new BABYLON.VertexData();
			vertexData.positions = positions;
			vertexData.indices = cad.triangles;
			vertexData.applyToMesh(m);
			m.material=myMat;
			var maxxyz=Math.max(m.getBoundingInfo().boundingBox.extendSize.x,m.getBoundingInfo().boundingBox.extendSize.y,m.getBoundingInfo().boundingBox.extendSize.z)
			camera.radius=maxxyz*3;                //radius instead of position vector is changed to zoom in all the parts.
			//end of direct mesh loading
			*/
            return scene;
        };
        /******* End of the create scene function ******/

		function loadPart3D(partid,token) {
			$.post("api.php",
			{
				action: "getPart3D",
				token: token,
				partid: partid
			},
			function(data, status){
				//var i=document.getElementById('new_partpreview');
				var c=document.getElementById('renderCanvas');
				
				if ((typeof(data)=="string") && //no cad data case
					((data.indexOf("Error")==0) || (data.indexOf("no cad data")==0)))
				{
					//var p=document.getElementById('partselector');
					//i.style.backgroundImage='url(\'image.php?part='+p.value+'\')';
					//i.style.display="";
					c.style.display="none";
				}
				else //there is proper cad data
				{
				 //i.style.display="none";
				 c.style.display="";
				 scene = createSceneScrew(data);
				 engine.runRenderLoop(function () {
                 scene.render();
				 });
				}
			});  

		}

        var scene = createScene(); //Call the createScene function

        // Register a render loop to repeatedly render the scene
        engine.runRenderLoop(function () {
                scene.render();
        });

        // Watch for browser/canvas resize events
        window.addEventListener("resize", function () {
                engine.resize();
        });